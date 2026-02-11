<script>
  import { onMount, onDestroy, tick } from 'svelte';
  import { slide } from 'svelte/transition';
  import { Calendar } from '@fullcalendar/core';
  import dayGridPlugin from '@fullcalendar/daygrid';
  import interactionPlugin from '@fullcalendar/interaction';
  import esLocale from '@fullcalendar/core/locales/es';
  import { scheduledVisitsAPI, blockedDaysAPI } from '../../../services/api';
  import Card from '../../../components/Card.svelte';
  import Badge from '../../../components/Badge.svelte';
  import Modal from '../../../components/Modal.svelte';
  import AgendarVisitaModal from '../../../components/AgendarVisitaModal.svelte';
  import ReagendarVisitaModal from '../../../components/ReagendarVisitaModal.svelte';
  
  let visitas = [];
  let visitasFiltradas = [];
  let visitasHoy = [];
  let visitasProximas = [];
  let visitasPasadas = [];
  let loading = true;
  let error = null;
  let showAgendarModal = false;
  let filtroFecha = '';
  let filtroEstado = 'programada';
  let visitasAbiertas = new Set(); // IDs de visitas expandidas
  let filtrosAbiertos = false; // Estado de filtros colapsables
  let vistaActiva = 'calendario'; // 'lista' | 'calendario' — por defecto calendario para el admin
  let calendarEl;
  let calendar = null;
  let occupiedDates = new Set();
  /** Rango actual solicitado (from-to) para ignorar respuestas de peticiones obsoletas. */
  let availabilityRequestKey = '';
  let availabilityDebounceTimer = null;
  const AVAILABILITY_DEBOUNCE_MS = 280;
  
  // Estado del modal de reagendar
  let visitaSeleccionada = null;
  let showReagendarModal = false;
  
  // Modal detalle de visita (entrar a la reserva) y cancelar
  let visitaDetalle = null;
  let showDetalleModal = false;
  let showCancelConfirmModal = false;
  let submittingCancel = false;
  let detalleError = null;

  // Días bloqueados (admin) - nadie puede reservar
  let blockedDays = [];
  let blockedDateSet = new Set();
  let showBloquearModal = false;
  let bloqueoDate = '';
  let bloqueoDescription = 'Feriado';
  let submittingBloqueo = false;
  let bloqueoError = null;
  
  onMount(async () => {
    await cargarVisitas();
  });

  onDestroy(() => {
    if (availabilityDebounceTimer) {
      clearTimeout(availabilityDebounceTimer);
      availabilityDebounceTimer = null;
    }
    if (calendar) {
      calendar.destroy();
      calendar = null;
    }
  });

  function visitasToEvents() {
    return visitas
      .filter(v => v && (v.status === 'programada' || v.status === 'completada' || v.status === 'cancelada') && v.scheduled_date)
      .map(v => ({
        id: String(v.id),
        title: v.status === 'cancelada'
          ? (v.cliente_nombre || v.direccion || `Visita #${v.id}`) + ' (cancelada)'
          : (v.cliente_nombre || v.direccion || `Visita #${v.id}`),
        start: v.scheduled_date,
        allDay: true,
        extendedProps: { status: v.status, visita: v },
      }));
  }

  function applyDayCellClassNames() {
    if (!calendar) return;
    calendar.setOption('dayCellClassNames', (arg) => {
      const c = [];
      if (blockedDateSet.has(arg.dateStr)) c.push('fc-day-blocked');
      else if (occupiedDates.has(arg.dateStr)) c.push('fc-day-occupied');
      return c;
    });
    try {
      const view = calendar.view ?? calendar.getCurrentData()?.viewApi;
      if (view?.type) calendar.changeView(view.type);
    } catch (_) {}
    // Pintar amarillo en el DOM después del re-render (por si dayCellDidMount no se vuelve a ejecutar)
    setTimeout(() => paintBlockedDaysInCalendar(), 50);
  }

  /** Pinta amarillo las celdas bloqueadas y QUITA el amarillo de las desbloqueadas (admin). */
  function paintBlockedDaysInCalendar() {
    if (!calendarEl) return;
    const paintCell = (el) => {
      el.style.setProperty('background-color', '#d97706', 'important');
      el.style.setProperty('cursor', 'not-allowed');
      el.querySelectorAll('*').forEach((child) => {
        child.style.setProperty('background-color', '#d97706', 'important');
      });
    };
    const clearCell = (el) => {
      el.style.removeProperty('background-color');
      el.style.removeProperty('cursor');
      el.querySelectorAll('*').forEach((child) => {
        if (child.classList.contains('fc-event') || child.closest('.fc-event')) return;
        child.style.removeProperty('background-color');
      });
    };
    // Todas las celdas de día (FullCalendar usa data-date en el td)
    calendarEl.querySelectorAll('td[data-date]').forEach((el) => {
      const dateStr = el.getAttribute('data-date');
      if (!dateStr) return;
      if (blockedDateSet.has(dateStr)) {
        paintCell(el);
      } else {
        clearCell(el);
      }
    });
    // Por si alguna celda no tiene data-date pero sí la clase
    calendarEl.querySelectorAll('.fc-day-blocked').forEach(paintCell);
  }

  async function fetchAvailability(from, to) {
    if (!from || !to) return;
    const key = `${from}-${to}`;
    availabilityRequestKey = key;
    try {
      const res = await scheduledVisitsAPI.getAvailability(from, to);
      if (availabilityRequestKey !== key) return;
      if (res.success && res.data?.occupied_dates) {
        occupiedDates = new Set(res.data.occupied_dates);
      } else {
        occupiedDates = new Set();
      }
      applyDayCellClassNames();
    } catch (e) {
      if (availabilityRequestKey !== key) return;
      occupiedDates = new Set();
    }
  }

  async function fetchBlockedDays(from, to) {
    if (!from || !to) return;
    try {
      const res = await blockedDaysAPI.getByRange(from, to);
      if (res.success && Array.isArray(res.data)) {
        blockedDays = res.data;
        blockedDateSet = new Set(blockedDays.map((b) => String(b.blocked_date).slice(0, 10)));
      } else {
        blockedDays = [];
        blockedDateSet = new Set();
      }
      applyDayCellClassNames();
    } catch (e) {
      blockedDays = [];
      blockedDateSet = new Set();
    }
  }

  /** Llamar disponibilidad y días bloqueados con debounce (para datesSet al cambiar de mes). */
  function fetchAvailabilityDebounced(from, to) {
    if (availabilityDebounceTimer) clearTimeout(availabilityDebounceTimer);
    availabilityDebounceTimer = setTimeout(async () => {
      availabilityDebounceTimer = null;
      await fetchAvailability(from, to);
      await fetchBlockedDays(from, to);
    }, AVAILABILITY_DEBOUNCE_MS);
  }

  async function mostrarCalendario() {
    vistaActiva = 'calendario';
    await tick();
    if (!calendarEl) return;
    const now = new Date();
    const viewStart = new Date(now.getFullYear(), now.getMonth(), 1);
    const viewEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    const fromStr = viewStart.toISOString().slice(0, 10);
    const toStr = viewEnd.toISOString().slice(0, 10);

    if (calendar) {
      calendar.removeAllEvents();
      calendar.addEventSource(visitasToEvents());
      await fetchAvailability(fromStr, toStr);
      await fetchBlockedDays(fromStr, toStr);
      return;
    }
    // Primera vez: cargar días bloqueados y disponibilidad ANTES de crear el calendario para que el amarillo aparezca ya
    await fetchAvailability(fromStr, toStr);
    await fetchBlockedDays(fromStr, toStr);

    calendar = new Calendar(calendarEl, {
      plugins: [dayGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      locale: esLocale,
      firstDay: 1,
      headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
      height: 'auto',
      events: visitasToEvents(),
      dayCellClassNames: (arg) => {
        const c = [];
        if (blockedDateSet.has(arg.dateStr)) c.push('fc-day-blocked');
        else if (occupiedDates.has(arg.dateStr)) c.push('fc-day-occupied');
        return c;
      },
      dayCellDidMount: (arg) => {
        const d = arg.date;
        const dateStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        if (blockedDateSet.has(dateStr)) {
          const el = arg.el;
          el.style.setProperty('background-color', '#d97706', 'important');
          el.style.setProperty('cursor', 'not-allowed');
          el.querySelectorAll('*').forEach((child) => {
            child.style.setProperty('background-color', '#d97706', 'important');
          });
        }
      },
      datesSet: (info) => {
        const start = info?.start ?? info?.view?.activeStart;
        const end = info?.end ?? info?.view?.activeEnd;
        if (!start || !end) return;
        fetchAvailabilityDebounced(start.toISOString().slice(0, 10), end.toISOString().slice(0, 10));
      },
      eventDidMount: (arg) => {
        const status = arg.event.extendedProps.status;
        if (status === 'completada') {
          arg.el.style.backgroundColor = '#10b981';
          arg.el.style.borderColor = '#059669';
        } else if (status === 'cancelada') {
          arg.el.style.backgroundColor = '#dc2626';
          arg.el.style.borderColor = '#b91c1c';
          arg.el.style.opacity = '0.9';
        }
        arg.el.style.cursor = 'pointer';
      },
      eventClick: (info) => {
        info.jsEvent.preventDefault();
        const v = info.event.extendedProps?.visita;
        if (v) {
          visitaDetalle = v;
          detalleError = null;
          showDetalleModal = true;
        }
      },
    });
    calendar.render();
    calendar.removeAllEvents();
    calendar.addEventSource(visitasToEvents());
    setTimeout(() => paintBlockedDaysInCalendar(), 50);
  }

  function mostrarLista() {
    vistaActiva = 'lista';
    if (calendar) {
      calendar.destroy();
      calendar = null;
    }
  }

  /** Refresca los eventos del calendario cuando cambian visitas o la vista. */
  $: if (vistaActiva === 'calendario' && calendar && visitas) {
    calendar.removeAllEvents();
    calendar.addEventSource(visitasToEvents());
  }
  
  /** @param {boolean} [showLoading=true] - Si es false, no muestra el spinner (evita desmontar el calendario). */
  async function cargarVisitas(showLoading = true) {
    try {
      if (showLoading) loading = true;
      const response = await scheduledVisitsAPI.getAll();
      if (response.success) {
        const raw = response.data;
        visitas = Array.isArray(raw) ? raw : [];
        if (import.meta.env.DEV) {
          console.log('[Agenda] Visitas cargadas:', visitas.length, visitas.length ? '(revisa el calendario)' : '— Si es 0, el backend no devuelve visitas para este usuario/sesión.');
        }
        if (visitas.length > 0) {
          visitas.sort((a, b) => {
            const d = new Date(a.scheduled_date || 0) - new Date(b.scheduled_date || 0);
            if (d !== 0) return d;
            // Mismo día: ordenar por horario
            const toMinutes = (t) => {
              if (!t) return 24 * 60 + 59;
              const [h, m] = String(t).split(':').map(Number);
              return (h || 0) * 60 + (m || 0);
            };
            return toMinutes(a.scheduled_time) - toMinutes(b.scheduled_time);
          });
        }
        error = null;
        // Si ya estamos en vista calendario, forzar que el calendario muestre los eventos recién cargados
        if (vistaActiva === 'calendario' && calendar) {
          await tick();
          refrescarCalendario();
        }
      }
    } catch (err) {
      console.error('Error cargando visitas:', err);
      error = 'Error al cargar las visitas programadas. Verifica que el backend esté corriendo.';
    } finally {
      if (showLoading) loading = false;
      // Si la vista activa es calendario, inicializarlo después de que el DOM tenga calendarEl (solo al cargar la página)
      if (vistaActiva === 'calendario') {
        tick().then(() => {
          if (calendarEl && !calendar) mostrarCalendario();
        });
      }
    }
  }
  
  async function eliminarVisita(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta visita programada?')) {
      return;
    }

    try {
      const response = await scheduledVisitsAPI.delete(id);
      if (response.success) {
        await cargarVisitas(false);
        refrescarCalendario();
      }
    } catch (err) {
      console.error('Error eliminando visita:', err);
      alert('Error al eliminar la visita');
    }
  }
  
  function refrescarCalendario() {
    if (vistaActiva === 'calendario' && calendar) {
      calendar.removeAllEvents();
      calendar.addEventSource(visitasToEvents());
    }
  }

  async function marcarCompletada(id) {
    try {
      const response = await scheduledVisitsAPI.update(id, { status: 'completada' });
      if (response.success) {
        await cargarVisitas(false); // no mostrar spinner para no desmontar el calendario
        refrescarCalendario();
      } else {
        throw new Error(response.message || 'Error al actualizar la visita');
      }
    } catch (err) {
      console.error('Error actualizando visita:', err);
      error = err.message || 'Error al marcar la visita como completada';
      alert(error);
    }
  }
  
  function toggleVisita(id) {
    if (visitasAbiertas.has(id)) {
      visitasAbiertas.delete(id);
    } else {
      visitasAbiertas.add(id);
    }
    visitasAbiertas = visitasAbiertas; // Trigger reactivity
  }

  function abrirReagendarModal(visita) {
    visitaSeleccionada = visita;
    showReagendarModal = true;
  }

  async function handleReagendarSuccess() {
    showReagendarModal = false;
    visitaSeleccionada = null;
    await cargarVisitas(false);
    refrescarCalendario();
  }

  function abrirDetalle(visita) {
    visitaDetalle = visita;
    detalleError = null;
    showDetalleModal = true;
  }

  function cerrarDetalle() {
    if (!submittingCancel) {
      showDetalleModal = false;
      showCancelConfirmModal = false;
      visitaDetalle = null;
      detalleError = null;
    }
  }

  async function cancelarVisita() {
    if (!visitaDetalle) return;
    submittingCancel = true;
    detalleError = null;
    try {
      await scheduledVisitsAPI.update(visitaDetalle.id, { status: 'cancelada' });
      await cargarVisitas(false);
      refrescarCalendario();
      showCancelConfirmModal = false;
      showDetalleModal = false;
      visitaDetalle = null;
      detalleError = null;
    } catch (e) {
      detalleError = e.message || 'Error al cancelar la visita.';
    } finally {
      submittingCancel = false;
    }
  }

  function abrirConfirmCancelar() {
    showCancelConfirmModal = true;
  }

  function cerrarConfirmCancelar() {
    if (!submittingCancel) showCancelConfirmModal = false;
  }

  function abrirBloquearModal() {
    bloqueoDate = '';
    bloqueoDescription = 'Feriado';
    bloqueoError = null;
    showBloquearModal = true;
  }

  async function guardarBloqueo() {
    if (!bloqueoDate) {
      bloqueoError = 'Elegí una fecha.';
      return;
    }
    submittingBloqueo = true;
    bloqueoError = null;
    try {
      await blockedDaysAPI.create(bloqueoDate, bloqueoDescription || '');
      showBloquearModal = false;
      const current = calendar?.getCurrentData()?.dateProfile?.currentRange?.start ?? new Date();
      const start = new Date(current.getFullYear(), current.getMonth(), 1);
      const end = new Date(current.getFullYear(), current.getMonth() + 1, 0);
      await fetchBlockedDays(start.toISOString().slice(0, 10), end.toISOString().slice(0, 10));
    } catch (e) {
      bloqueoError = e.message || 'Error al bloquear el día.';
    } finally {
      submittingBloqueo = false;
    }
  }

  async function eliminarBloqueo(id) {
    try {
      await blockedDaysAPI.delete(id);
      const current = calendar?.getCurrentData()?.dateProfile?.currentRange?.start ?? new Date();
      const start = new Date(current.getFullYear(), current.getMonth(), 1);
      const end = new Date(current.getFullYear(), current.getMonth() + 1, 0);
      await fetchBlockedDays(start.toISOString().slice(0, 10), end.toISOString().slice(0, 10));
    } catch (e) {
      console.error(e);
    }
  }
  
  function getBadgeType(status) {
    if (status === 'completada') return 'success';
    if (status === 'cancelada') return 'danger';
    return 'info';
  }
  
  function getStatusLabel(status) {
    const labels = {
      'programada': 'Programada',
      'completada': 'Completada',
      'cancelada': 'Cancelada'
    };
    return labels[status] || status;
  }
  
  function formatearFecha(fecha) {
    const date = new Date(fecha);
    return date.toLocaleDateString('es-AR', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
  }
  
  function formatearFechaCorta(fecha) {
    const date = new Date(fecha);
    return date.toLocaleDateString('es-AR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  }
  
  function esHoy(fecha) {
    const hoy = new Date();
    const fechaVisita = new Date(fecha);
    return hoy.toDateString() === fechaVisita.toDateString();
  }
  
  function esPasada(fecha) {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fechaVisita = new Date(fecha);
    fechaVisita.setHours(0, 0, 0, 0);
    return fechaVisita < hoy;
  }
  
  /** Un solo pase: filtrado + clasificación en hoy / próximas / pasadas. */
  $: {
    const filtradas = [];
    const hoy = [];
    const proximas = [];
    const pasadas = [];
    for (const visita of visitas) {
      if (!visita || !visita.scheduled_date) continue;
      const matchesEstado = filtroEstado === 'todas' || visita.status === filtroEstado;
      const matchesFecha = !filtroFecha || String(visita.scheduled_date).startsWith(filtroFecha);
      if (!matchesEstado || !matchesFecha) continue;
      filtradas.push(visita);
      const isHoy = esHoy(visita.scheduled_date);
      const isPast = esPasada(visita.scheduled_date);
      const isProgramada = visita.status === 'programada';
      if (isHoy && isProgramada) hoy.push(visita);
      else if (!isPast && !isHoy && isProgramada) proximas.push(visita);
      else pasadas.push(visita);
    }
    visitasFiltradas = filtradas;
    visitasHoy = hoy;
    visitasProximas = proximas;
    visitasPasadas = pasadas;
  }

  // Contar filtros activos
  $: filtrosActivos = (filtroFecha ? 1 : 0) + (filtroEstado !== 'programada' ? 1 : 0);
</script>

<svelte:head>
  <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.min.css" rel="stylesheet" />
</svelte:head>

<div class="py-6">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex items-center gap-3 flex-wrap">
      <h2 class="text-2xl font-semibold text-gray-700">
        Agenda de Visitas
      </h2>
      <div class="flex rounded-lg border border-gray-200 p-0.5 bg-gray-50">
        <button
          on:click={mostrarLista}
          class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {vistaActiva === 'lista' ? 'bg-white text-primary-600 shadow' : 'text-gray-600 hover:text-gray-900'}"
        >
          Lista
        </button>
        <button
          on:click={mostrarCalendario}
          class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {vistaActiva === 'calendario' ? 'bg-white text-primary-600 shadow' : 'text-gray-600 hover:text-gray-900'}"
        >
          Calendario
        </button>
      </div>
    </div>
    <button
      on:click={() => showAgendarModal = true}
      class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center justify-center gap-2"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      <span class="hidden sm:inline">Agendar Nueva Visita</span>
      <span class="sm:hidden">Agendar</span>
    </button>
  </div>

  {#if error}
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
      {error}
    </div>
  {/if}

  <!-- Filtros Colapsables (solo en vista lista) -->
  {#if vistaActiva === 'lista'}
  <Card className="mb-6">
    <button
      on:click={() => filtrosAbiertos = !filtrosAbiertos}
      class="w-full flex items-center justify-between rounded-lg transition-colors md:hidden"
    >
      <div class="flex items-center gap-3">
        <h3 class="text-lg font-semibold text-gray-900">Filtros</h3>
        {#if filtrosActivos > 0}
          <Badge type="info">{filtrosActivos} activo{filtrosActivos > 1 ? 's' : ''}</Badge>
        {/if}
      </div>
      <svg
        class="w-5 h-5 text-gray-500 transition-transform {filtrosAbiertos ? 'rotate-180' : ''}"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    
    <!-- Título en desktop (siempre visible) -->
    <!-- <div class="hidden md:block">
      <h3 class="text-lg font-semibold text-gray-900">Filtros</h3>
    </div> -->
    
    <!-- Contenido de filtros: colapsable en mobile, siempre visible en desktop -->
    <div class="hidden md:block">
      <div class="pb-4 space-y-4">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <label for="filtroFecha" class="block text-sm font-medium text-gray-700 mb-2">
              Filtrar por fecha
            </label>
            <input
              id="filtroFecha"
              type="date"
              bind:value={filtroFecha}
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>
          <div>
            <label for="filtroEstado" class="block text-sm font-medium text-gray-700 mb-2">
              Estado
            </label>
            <select
              id="filtroEstado"
              bind:value={filtroEstado}
              class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="todas">Todas</option>
              <option value="programada">Programadas</option>
              <option value="completada">Completadas</option>
              <option value="cancelada">Canceladas</option>
            </select>
          </div>
          {#if filtroFecha || filtroEstado !== 'programada'}
            <div class="flex items-end">
              <button
                on:click={() => { filtroFecha = ''; filtroEstado = 'programada'; }}
                class="w-full md:w-auto px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Limpiar filtros
              </button>
            </div>
          {/if}
        </div>
      </div>
    </div>
    
    {#if filtrosAbiertos}
      <div transition:slide class="md:hidden">
        <div class="pb-4 space-y-4 pt-4">
          <div class="flex flex-col gap-4">
            <div class="flex-1">
              <label for="filtroFecha-mobile" class="block text-sm font-medium text-gray-700 mb-2">
                Filtrar por fecha
              </label>
              <input
                id="filtroFecha-mobile"
                type="date"
                bind:value={filtroFecha}
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
            </div>
            <div>
              <label for="filtroEstado-mobile" class="block text-sm font-medium text-gray-700 mb-2">
                Estado
              </label>
              <select
                id="filtroEstado-mobile"
                bind:value={filtroEstado}
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="todas">Todas</option>
                <option value="programada">Programadas</option>
                <option value="completada">Completadas</option>
                <option value="cancelada">Canceladas</option>
              </select>
            </div>
            {#if filtroFecha || filtroEstado !== 'programada'}
              <div class="flex items-end">
                <button
                  on:click={() => { filtroFecha = ''; filtroEstado = 'programada'; }}
                  class="w-full px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                  Limpiar filtros
                </button>
              </div>
            {/if}
          </div>
        </div>
      </div>
    {/if}
  </Card>
  {/if}

  {#if loading}
    <div class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      <p class="ml-4 text-gray-600">Cargando agenda...</p>
    </div>
  {:else}
    {#if vistaActiva === 'calendario'}
      <Card className="mb-6">
        <div class="flex flex-col sm:flex-row flex-wrap justify-center items-center sm:justify-between gap-2 px-3 sm:px-4 pt-3 sm:pt-4 pb-1">
          <p class="text-sm text-gray-500 text-center sm:text-left flex flex-wrap justify-center items-center gap-x-3 gap-y-1">
            <span class="inline-flex items-center gap-1">Próximas <span class="inline-block w-3 h-3 rounded-full bg-blue-600 align-middle"></span></span><span class="inline-flex items-center gap-1">Completada <span class="inline-block w-3 h-3 rounded-full bg-green-600 align-middle"></span></span><span class="inline-flex items-center gap-1">Cancelada <span class="inline-block w-3 h-3 rounded-full bg-red-600 align-middle"></span></span><span class="inline-flex items-center gap-1">Día bloqueado <span class="inline-block w-3 h-3 rounded-full bg-amber-500 align-middle"></span></span>
          </p>
          <button
            type="button"
            on:click={abrirBloquearModal}
            class="px-3 py-1.5 text-sm border border-amber-600 text-amber-700 rounded-lg hover:bg-amber-50 transition-colors whitespace-nowrap"
          >
            Bloquear día
          </button>
        </div>
        <div class="overflow-x-auto p-2 pb-0 sm:p-4 sm:pb-4">
          <div bind:this={calendarEl} class="admin-agenda-calendar min-w-[280px]"></div>
        </div>
        {#if blockedDays.length > 0}
          <div class="px-3 sm:px-4 pb-3 pt-2 border-t border-gray-100 mt-2">
            <p class="text-sm font-medium text-gray-700 mb-2">Días bloqueados este mes</p>
            <ul class="space-y-1.5">
              {#each blockedDays as b (b.id)}
                <li class="flex items-center justify-between gap-2 text-sm bg-amber-50 border border-amber-200 rounded px-3 py-2">
                  <span><strong>{formatearFechaCorta(b.blocked_date)}</strong> – {b.description || 'Sin descripción'}</span>
                  <button
                    type="button"
                    on:click={() => eliminarBloqueo(b.id)}
                    class="text-red-600 hover:text-red-800 text-xs font-medium"
                    title="Desbloquear"
                  >
                    Desbloquear
                  </button>
                </li>
              {/each}
            </ul>
          </div>
        {/if}
      </Card>
    {:else}
    <!-- Vista Lista: tabla (desktop) + lista colapsable (mobile) -->
    {#if visitasFiltradas.length > 0}
      <!-- Tabla: solo desktop -->
      <Card className="mb-6 hidden md:block">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dirección</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jardinero</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              {#each visitasFiltradas as visita (visita.id)}
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{formatearFechaCorta(visita.scheduled_date)}</div>
                    {#if visita.scheduled_time}
                      <div class="text-xs text-gray-500">{visita.scheduled_time}</div>
                    {/if}
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm text-gray-900">{visita.cliente_nombre || '—'}</div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm text-gray-600 max-w-xs truncate" title={visita.direccion || ''}>{visita.direccion || '—'}</div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{visita.gardener_name || '—'}</div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <Badge type={getBadgeType(visita.status)}>{getStatusLabel(visita.status)}</Badge>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center gap-1 flex-wrap">
                      <button
                        on:click={() => abrirDetalle(visita)}
                        disabled={loading}
                        class="p-1.5 text-primary-600 hover:bg-primary-50 rounded disabled:opacity-50"
                        title="Ver detalle"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                      {#if visita.status === 'programada'}
                        <button
                          on:click={() => abrirReagendarModal(visita)}
                          disabled={loading}
                          class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded disabled:opacity-50"
                          title="Postergar por lluvia"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                          </svg>
                        </button>
                        <button
                          on:click={() => marcarCompletada(visita.id)}
                          disabled={loading}
                          class="p-1.5 text-green-600 hover:bg-green-50 rounded disabled:opacity-50"
                          title="Marcar como completada"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                        </button>
                      {/if}
                      <button
                        on:click={() => eliminarVisita(visita.id)}
                        disabled={loading}
                        class="p-1.5 text-red-600 hover:bg-red-50 rounded disabled:opacity-50"
                        title="Eliminar"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              {/each}
            </tbody>
          </table>
        </div>
      </Card>

      <!-- Lista colapsable: solo mobile -->
      <div class="block md:hidden mb-6">
        <Card>
          <div class="space-y-3">
            {#each visitasFiltradas as visita (visita.id)}
              <div class="border border-gray-200 rounded-lg overflow-hidden min-w-0">
                <button
                  on:click={() => toggleVisita(visita.id)}
                  class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors flex items-center justify-between gap-2 text-left"
                >
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 text-sm truncate">{visita.cliente_nombre || '—'}</p>
                    <p class="text-xs text-gray-600 mt-0.5">{formatearFechaCorta(visita.scheduled_date)}{visita.scheduled_time ? ` · ${visita.scheduled_time}` : ''}</p>
                  </div>
                  <Badge type={getBadgeType(visita.status)} class="shrink-0">{getStatusLabel(visita.status)}</Badge>
                  <svg
                    class="w-5 h-5 text-gray-500 shrink-0 transition-transform {visitasAbiertas.has(visita.id) ? 'rotate-180' : ''}"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                {#if visitasAbiertas.has(visita.id)}
                  <div class="px-4 py-4 bg-white border-t border-gray-200 space-y-3">
                    <p class="text-sm text-gray-700"><strong>Dirección:</strong> {visita.direccion || '—'}</p>
                    {#if visita.gardener_name}
                      <p class="text-sm text-gray-700"><strong>Jardinero:</strong> {visita.gardener_name}</p>
                    {/if}
                    {#if visita.notes}
                      <p class="text-sm text-gray-600 italic">"{visita.notes}"</p>
                    {/if}
                    <div class="flex flex-wrap gap-2 pt-2">
                      <button
                        on:click={() => abrirDetalle(visita)}
                        disabled={loading}
                        class="flex-1 min-w-[120px] px-3 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center justify-center gap-1"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        Ver detalle
                      </button>
                      {#if visita.status === 'programada'}
                        <button
                          on:click={() => abrirReagendarModal(visita)}
                          disabled={loading}
                          class="flex-1 min-w-[120px] px-3 py-2 text-sm bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 disabled:opacity-50 flex items-center justify-center gap-1"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                          Postergar
                        </button>
                        <button
                          on:click={() => marcarCompletada(visita.id)}
                          disabled={loading}
                          class="flex-1 min-w-[120px] px-3 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 flex items-center justify-center gap-1"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                          Completar
                        </button>
                      {/if}
                      <button
                        on:click={() => eliminarVisita(visita.id)}
                        disabled={loading}
                        class="flex-1 min-w-[120px] px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 flex items-center justify-center gap-1"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Eliminar
                      </button>
                    </div>
                  </div>
                {/if}
              </div>
            {/each}
          </div>
        </Card>
      </div>
    {/if}

    {#if visitasFiltradas.length === 0}
      <Card>
        <div class="text-center py-12">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p class="text-gray-500 text-lg mb-2">No hay visitas programadas</p>
          <p class="text-gray-400 text-sm">Agenda una nueva visita para comenzar</p>
        </div>
      </Card>
    {/if}
    {/if}
  {/if}
</div>

<style>
  :global(.admin-agenda-calendar) {
    --fc-border-color: #e5e7eb;
    --fc-button-bg-color: #2563eb;
    --fc-button-border-color: #2563eb;
    --fc-button-hover-bg-color: #1d4ed8;
    --fc-button-hover-border-color: #1d4ed8;
    min-height: 400px;
  }
  :global(.admin-agenda-calendar .fc-day-occupied) {
    background-color: #f3f4f6 !important;
    opacity: 0.85;
  }
  /* Título del mes: primera letra en mayúscula (ej. "Febrero de 2026") */
  :global(.admin-agenda-calendar .fc-toolbar-title) {
    text-transform: lowercase;
  }
  :global(.admin-agenda-calendar .fc-toolbar-title::first-letter) {
    text-transform: uppercase;
  }
  /* Día bloqueado: celda entera en amarillo para admin y que no se pueda agendar */
  :global(.admin-agenda-calendar td.fc-day-blocked),
  :global(.admin-agenda-calendar td.fc-day-blocked *),
  :global(.admin-agenda-calendar .fc-day-blocked),
  :global(.admin-agenda-calendar .fc-day-blocked .fc-scrollgrid-sync-inner),
  :global(.admin-agenda-calendar .fc-day-blocked .fc-daygrid-day-frame),
  :global(.admin-agenda-calendar .fc-day-blocked .fc-daygrid-day-events) {
    background-color: #d97706 !important;
    background-image: none !important;
    cursor: not-allowed !important;
  }
  :global(.admin-agenda-calendar .fc-day-blocked .fc-daygrid-day-number) {
    color: #fff !important;
    font-weight: 700;
  }
  :global(.admin-agenda-calendar .fc-day-blocked .fc-daygrid-day-frame) {
    position: relative;
    min-height: 100%;
  }
  :global(.admin-agenda-calendar .fc-day-blocked .fc-daygrid-day-frame::after) {
    content: 'Bloqueado';
    display: block;
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    text-align: center;
    font-size: 0.7rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.95);
    pointer-events: none;
  }
  /* Mobile-friendly */
  @media (max-width: 640px) {
    :global(.admin-agenda-calendar) {
      min-height: 320px;
      padding: 0.25rem !important;
      padding-bottom: 0 !important;
      margin-bottom: 0 !important;
    }
    :global(.admin-agenda-calendar .fc) {
      margin-bottom: 0 !important;
    }
    :global(.admin-agenda-calendar .fc-view-harness) {
      margin-bottom: 0 !important;
    }
    :global(.admin-agenda-calendar .fc-toolbar) {
      flex-direction: column;
      gap: 0.5rem;
      padding: 0.25rem 0;
    }
    :global(.admin-agenda-calendar .fc-toolbar-title) {
      font-size: 1rem;
      margin: 0;
    }
    :global(.admin-agenda-calendar .fc-button) {
      padding: 0.5rem 0.75rem;
      font-size: 0.875rem;
      min-height: 44px;
      min-width: 44px;
    }
    :global(.admin-agenda-calendar .fc-scrollgrid) {
      font-size: 0.75rem;
    }
    :global(.admin-agenda-calendar .fc-col-header-cell-cushion),
    :global(.admin-agenda-calendar .fc-daygrid-day-number) {
      padding: 0.35rem;
      font-size: 0.8rem;
    }
    :global(.admin-agenda-calendar .fc-daygrid-day) {
      min-height: 44px;
    }
    :global(.admin-agenda-calendar .fc-daygrid-event) {
      font-size: 0.65rem;
      padding: 0.15rem 0.25rem;
    }
  }
</style>

<!-- Modal bloquear día -->
<Modal
  isOpen={showBloquearModal}
  title="Bloquear día"
  onClose={() => { if (!submittingBloqueo) showBloquearModal = false; }}
  size="sm"
>
  <p class="text-sm text-gray-600 mb-4">Nadie podrá reservar un turno en la fecha elegida (ej: feriado, mantenimiento).</p>
  <div class="space-y-3">
    <div>
      <label for="bloqueo-date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
      <input
        id="bloqueo-date"
        type="date"
        bind:value={bloqueoDate}
        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
      />
    </div>
    <div>
      <label for="bloqueo-desc" class="block text-sm font-medium text-gray-700 mb-1">Descripción (ej: Feriado)</label>
      <input
        id="bloqueo-desc"
        type="text"
        bind:value={bloqueoDescription}
        placeholder="Feriado"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
      />
    </div>
    {#if bloqueoError}
      <p class="text-sm text-red-600">{bloqueoError}</p>
    {/if}
    <div class="flex gap-2 justify-end pt-2">
      <button
        type="button"
        on:click={() => { if (!submittingBloqueo) showBloquearModal = false; }}
        disabled={submittingBloqueo}
        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
      >
        Cerrar
      </button>
      <button
        type="button"
        on:click={guardarBloqueo}
        disabled={submittingBloqueo}
        class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:opacity-50"
      >
        {#if submittingBloqueo}Guardando...{:else}Bloquear día{/if}
      </button>
    </div>
  </div>
</Modal>

<!-- Modal detalle de visita (entrar a la reserva) -->
<Modal
  isOpen={showDetalleModal}
  title={visitaDetalle ? `Visita – ${visitaDetalle.cliente_nombre || visitaDetalle.direccion || '#' + visitaDetalle.id}` : 'Detalle de la visita'}
  onClose={cerrarDetalle}
  size="sm"
>
  <div>
    {#if visitaDetalle}
      <p class="text-gray-700 mb-1"><strong>Cliente:</strong> {visitaDetalle.cliente_nombre || '—'}</p>
      <p class="text-gray-700 mb-1"><strong>Dirección:</strong> {visitaDetalle.direccion || '—'}</p>
      {#if visitaDetalle.direccion && visitaDetalle.direccion.trim() && visitaDetalle.direccion !== 'N/A'}
        <p class="text-gray-700 mb-2 -mt-0.5">
          <a
            href="https://www.google.com/maps?q={encodeURIComponent(visitaDetalle.direccion)}"
            target="_blank"
            rel="noopener noreferrer"
            class="text-primary-600 hover:text-primary-700 text-sm font-medium inline-flex items-center gap-1"
          >
            Ver con Google Maps
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
          </a>
        </p>
      {/if}
      <p class="text-gray-700 mb-1"><strong>Fecha:</strong> {formatearFecha(visitaDetalle.scheduled_date)}</p>
      <p class="text-gray-700 mb-1"><strong>Horario:</strong> {visitaDetalle.scheduled_time || '—'}</p>
      <p class="text-gray-700 mb-3"><strong>Estado:</strong> {getStatusLabel(visitaDetalle.status)}</p>
      {#if visitaDetalle.notes}
        <p class="text-sm text-gray-600 mb-4 italic">"{visitaDetalle.notes}"</p>
      {/if}
      {#if detalleError}
        <p class="text-sm text-red-600 mb-3">{detalleError}</p>
      {/if}
      <div class="flex flex-wrap gap-2">
        {#if visitaDetalle.status === 'programada'}
          <button
            type="button"
            on:click={() => { showDetalleModal = false; abrirReagendarModal(visitaDetalle); }}
            disabled={submittingCancel}
            class="px-3 py-1.5 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600"
          >
            Postergar por lluvia
          </button>
          <button
            type="button"
            on:click={async () => { await marcarCompletada(visitaDetalle.id); cerrarDetalle(); }}
            disabled={loading || submittingCancel}
            class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700"
          >
            Completar
          </button>
          <button
            type="button"
            on:click={abrirConfirmCancelar}
            disabled={submittingCancel}
            class="px-3 py-1.5 text-sm border border-red-600 text-red-600 rounded hover:bg-red-50"
          >
            Cancelar visita
          </button>
        {/if}
        <button
          type="button"
          on:click={async () => { if (confirm('¿Eliminar esta visita?')) { await eliminarVisita(visitaDetalle.id); cerrarDetalle(); } }}
          disabled={loading || submittingCancel}
          class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700"
        >
          Eliminar
        </button>
        <button
          type="button"
          on:click={cerrarDetalle}
          disabled={submittingCancel}
          class="px-3 py-1.5 text-sm border border-gray-300 rounded text-gray-700 hover:bg-gray-50"
        >
          Cerrar
        </button>
      </div>
    {/if}
  </div>
</Modal>

<!-- Modal confirmar cancelación (admin) -->
<Modal
  isOpen={showCancelConfirmModal}
  title="Cancelar visita"
  onClose={cerrarConfirmCancelar}
  size="sm"
>
  <p class="text-gray-700 mb-6">¿Estás seguro de que querés cancelar esta visita?</p>
  <div class="flex gap-3 justify-end">
    <button
      type="button"
      on:click={cerrarConfirmCancelar}
      disabled={submittingCancel}
      class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
    >
      No
    </button>
    <button
      type="button"
      on:click={cancelarVisita}
      disabled={submittingCancel}
      class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center justify-center gap-2"
    >
      {#if submittingCancel}
        <span class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
      {/if}
      Sí, cancelar
    </button>
  </div>
</Modal>

<!-- Modal Agendar Visita -->
<AgendarVisitaModal
  isOpen={showAgendarModal}
  onClose={() => showAgendarModal = false}
  onSuccess={async () => {
    showAgendarModal = false;
    await cargarVisitas(false);
  }}
/>

<!-- Modal Reagendar Visita -->
<ReagendarVisitaModal
  isOpen={showReagendarModal}
  visita={visitaSeleccionada}
  onClose={() => {
    showReagendarModal = false;
    visitaSeleccionada = null;
  }}
  onSuccess={handleReagendarSuccess}
/>
