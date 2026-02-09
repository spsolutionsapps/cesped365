<script>
  import { onMount, onDestroy } from 'svelte';
  import { Calendar } from '@fullcalendar/core';
  import dayGridPlugin from '@fullcalendar/daygrid';
  import interactionPlugin from '@fullcalendar/interaction';
  import esLocale from '@fullcalendar/core/locales/es';
  import { scheduledVisitsAPI, jardinesAPI } from '../../services/api';
  import Card from '../../components/Card.svelte';
  import Modal from '../../components/Modal.svelte';

  let calendarEl;
  let calendar = null;
  let occupiedDates = new Set();
  let blockedDates = new Set(); // días bloqueados por admin (ej: feriado) – se muestran en amarillo
  let jardines = [];
  let selectedTime = '09:00';
  let loading = true;
  const HORARIOS = [
    { value: '07:00', label: '07:00 – 09:00' },
    { value: '09:00', label: '09:00 – 11:00' },
    { value: '11:00', label: '11:00 – 13:00' },
    { value: '14:00', label: '14:00 – 16:00' },
    { value: '16:00', label: '16:00 – 18:00' },
  ];
  let loadingAvailability = false;
  let error = null;
  let showConfirmModal = false;
  let selectedDate = null;
  let takenSlotsForDate = []; // horarios ya ocupados para la fecha elegida
  let loadingSlots = false;
  let submitting = false;
  let successMessage = '';
  let misVisitas = []; // visitas programadas del cliente (para mostrar en el calendario)
  let selectedVisit = null; // visita clicada para ver detalle / cancelar / editar
  let showDetalleModal = false;
  let editMode = false;
  let editDate = '';
  let editTime = '09:00';
  let takenSlotsForEdit = [];
  let loadingEditSlots = false;
  let submittingCancel = false;
  let submittingEdit = false;
  let detalleError = null;
  let showCancelConfirmModal = false;

  let refetchIntervalId = null;
  let visibilityHandler = null;

  onMount(async () => {
    await cargarJardines();
    await cargarMisVisitas();
    const range = getMonthRange(new Date());
    await fetchAvailability(range.from, range.to);
    initCalendar();
    // Refetch cada 60 s para que si el admin bloquea días, el usuario los vea sin recargar
    refetchIntervalId = setInterval(() => {
      if (!calendar || document.visibilityState !== 'visible') return;
      const range = getMonthRange(calendar.getCurrentData()?.currentDate || new Date());
      fetchAvailability(range.from, range.to);
    }, 60000);
    visibilityHandler = () => {
      if (document.visibilityState === 'visible' && calendar) {
        const range = getMonthRange(calendar.getCurrentData()?.currentDate || new Date());
        fetchAvailability(range.from, range.to);
      }
    };
    document.addEventListener('visibilitychange', visibilityHandler);
  });

  onDestroy(() => {
    if (refetchIntervalId) clearInterval(refetchIntervalId);
    if (visibilityHandler) document.removeEventListener('visibilitychange', visibilityHandler);
    if (calendar) {
      calendar.destroy();
      calendar = null;
    }
  });

  async function cargarJardines() {
    try {
      const res = await jardinesAPI.getAll();
      if (res.success && res.data?.length) {
        jardines = res.data;
      }
    } catch (e) {
      console.error(e);
      error = 'No se pudieron cargar tus jardines.';
    } finally {
      loading = false;
    }
  }

  function getMonthRange(date) {
    const d = new Date(date);
    const start = new Date(d.getFullYear(), d.getMonth(), 1);
    const end = new Date(d.getFullYear(), d.getMonth() + 1, 0);
    return {
      from: start.toISOString().slice(0, 10),
      to: end.toISOString().slice(0, 10),
    };
  }

  /** Aplica amarillo a las celdas bloqueadas ya renderizadas (cliente). */
  function paintBlockedDaysInCalendar() {
    if (!calendarEl) return;
    const paintCell = (el) => {
      el.style.setProperty('background-color', '#d97706', 'important');
      el.style.setProperty('cursor', 'not-allowed');
      el.querySelectorAll('*').forEach((child) => {
        child.style.setProperty('background-color', '#d97706', 'important');
      });
    };
    calendarEl.querySelectorAll('.fc-day-blocked').forEach(paintCell);
    // Por si el refetch actualizó blockedDates pero la clase aún no se aplicó: pintar por data-date
    calendarEl.querySelectorAll('[data-date]').forEach((el) => {
      const dateStr = el.getAttribute('data-date');
      if (dateStr && blockedDates.has(dateStr)) paintCell(el);
    });
  }

  async function fetchAvailability(from, to) {
    if (!from || !to) return;
    loadingAvailability = true;
    try {
      const res = await scheduledVisitsAPI.getAvailability(from, to);
      if (res.success && res.data) {
        occupiedDates = new Set(res.data.occupied_dates || []);
        // Días bloqueados por admin: mismo amarillo que en agenda y no se puede agendar
        blockedDates = new Set(res.data.blocked_dates || []);
      } else {
        occupiedDates = new Set();
        blockedDates = new Set();
      }
      if (calendar) {
        calendar.setOption('dayCellClassNames', (arg) => {
          if (blockedDates.has(arg.dateStr)) return ['fc-day-blocked'];
          if (occupiedDates.has(arg.dateStr)) return ['fc-day-occupied'];
          return [];
        });
        try {
          const view = calendar.view;
          if (view && view.type) calendar.changeView(view.type);
        } catch (_) {}
        setTimeout(() => paintBlockedDaysInCalendar(), 50);
      }
    } catch (e) {
      console.error(e);
      occupiedDates = new Set();
      blockedDates = new Set();
    } finally {
      loadingAvailability = false;
    }
  }

  async function cargarMisVisitas() {
    try {
      const res = await scheduledVisitsAPI.getAll();
      if (res.success && Array.isArray(res.data)) {
        misVisitas = res.data;
      } else {
        misVisitas = [];
      }
      refreshCalendarEvents();
    } catch (e) {
      misVisitas = [];
    }
  }

  function misVisitasToEvents() {
    return misVisitas.map((v) => {
      const dateStr = v.scheduled_date ? String(v.scheduled_date).slice(0, 10) : '';
      const time = v.scheduled_time || '';
      const slotLabel = HORARIOS.find(h => h.value === time)?.label || time;
      const isCancelada = v.status === 'cancelada';
      const title = isCancelada
        ? (time ? `Tu visita (${slotLabel}) – cancelada` : 'Tu visita – cancelada')
        : (time ? `Tu visita (${slotLabel})` : 'Tu visita');
      return {
        id: `visit-${v.id}`,
        title,
        start: dateStr,
        allDay: true,
        display: 'block',
        classNames: isCancelada ? ['fc-event-mi-visita', 'fc-event-cancelada'] : ['fc-event-mi-visita'],
        extendedProps: { visitId: v.id, status: v.status },
      };
    });
  }

  function refreshCalendarEvents() {
    if (!calendar) return;
    calendar.getEventSources().forEach((s) => s.remove());
    calendar.addEventSource(misVisitasToEvents());
  }

  async function loadTakenSlotsForDate(dateStr) {
    if (!dateStr) return;
    loadingSlots = true;
    takenSlotsForDate = [];
    try {
      const res = await scheduledVisitsAPI.getAvailability(dateStr, dateStr);
      if (res.success && res.data?.occupied_slots_by_date?.[dateStr]) {
        takenSlotsForDate = res.data.occupied_slots_by_date[dateStr];
      }
      const firstFree = HORARIOS.find(h => !takenSlotsForDate.includes(h.value));
      selectedTime = firstFree ? firstFree.value : HORARIOS[0].value;
    } catch (e) {
      takenSlotsForDate = [];
    } finally {
      loadingSlots = false;
    }
  }

  function initCalendar() {
    if (!calendarEl) return;

    calendar = new Calendar(calendarEl, {
      plugins: [dayGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      locale: esLocale,
      firstDay: 1,
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: '',
      },
      height: 'auto',
      dayMaxEvents: false,
      dayCellClassNames: (arg) => {
        if (blockedDates.has(arg.dateStr)) return ['fc-day-blocked'];
        if (occupiedDates.has(arg.dateStr)) return ['fc-day-occupied'];
        return [];
      },
      dayCellDidMount: (arg) => {
        const d = arg.date;
        const dateStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        if (blockedDates.has(dateStr)) {
          const el = arg.el;
          el.style.setProperty('background-color', '#d97706', 'important');
          el.style.setProperty('cursor', 'not-allowed');
          el.querySelectorAll('*').forEach((child) => {
            child.style.setProperty('background-color', '#d97706', 'important');
          });
        }
      },
      dateClick: (info) => {
        if (blockedDates.has(info.dateStr)) {
          error = 'Ese día está bloqueado. No se pueden reservar turnos.';
          return;
        }
        if (occupiedDates.has(info.dateStr)) {
          return;
        }
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (info.date < today) {
          return;
        }
        if (!jardines.length) {
          error = 'No tenés ningún jardín registrado. No podés agendar una visita.';
          return;
        }
        selectedDate = info.dateStr;
        error = null;
        showConfirmModal = true;
        loadTakenSlotsForDate(info.dateStr);
      },
      datesSet: (info) => {
        const start = info?.start ?? info?.view?.activeStart;
        const end = info?.end ?? info?.view?.activeEnd;
        if (!start || !end) return;
        const fromStr = start.toISOString().slice(0, 10);
        const toStr = end.toISOString().slice(0, 10);
        fetchAvailability(fromStr, toStr);
      },
      eventClick: (info) => {
        info.jsEvent.preventDefault();
        const visitId = info.event.extendedProps?.visitId ?? (info.event.id ? parseInt(String(info.event.id).replace('visit-', ''), 10) : null);
        if (visitId == null || isNaN(visitId)) return;
        const v = misVisitas.find((x) => String(x.id) === String(visitId) || x.id === visitId);
        if (v) {
          selectedVisit = v;
          editMode = false;
          editDate = String(v.scheduled_date).slice(0, 10);
          editTime = v.scheduled_time || '09:00';
          detalleError = null;
          showDetalleModal = true;
        }
      },
      eventDidMount: (arg) => {
        const status = arg.event.extendedProps?.status;
        if (status === 'cancelada') {
          arg.el.style.backgroundColor = '#dc2626';
          arg.el.style.borderColor = '#b91c1c';
          arg.el.style.opacity = '0.95';
        }
        arg.el.style.cursor = 'pointer';
      },
    });

    calendar.render();
    calendar.addEventSource(misVisitasToEvents());
    const range = getMonthRange(new Date());
    fetchAvailability(range.from, range.to);
  }

  function formatFecha(str) {
    const d = new Date(str + 'T12:00:00');
    return d.toLocaleDateString('es-AR', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
  }

  async function confirmarReserva() {
    const gardenId = jardines[0]?.id;
    if (!selectedDate || !gardenId) {
      error = 'No se pudo identificar tu jardín. Recargá la página e intentá de nuevo.';
      return;
    }
    if (blockedDates.has(selectedDate)) {
      error = 'Ese día está bloqueado. No se pueden reservar turnos.';
      return;
    }
    submitting = true;
    error = null;
    successMessage = '';
    try {
      await scheduledVisitsAPI.create({
        garden_id: parseInt(gardenId, 10),
        scheduled_date: selectedDate,
        scheduled_time: selectedTime,
        notes: 'Reservado por el cliente desde la agenda.',
      });
      const slotLabel = HORARIOS.find(h => h.value === selectedTime)?.label || selectedTime;
      successMessage = `Visita reservada para el ${formatFecha(selectedDate)} (${slotLabel}).`;
      showConfirmModal = false;
      selectedDate = null;
      await cargarMisVisitas();
      const range = getMonthRange(new Date(calendar?.getCurrentData()?.currentDate || new Date()));
      await fetchAvailability(range.from, range.to);
    } catch (e) {
      error = e.message || 'Error al reservar la visita.';
      if (e.message && e.message.includes('horario ya está ocupado')) {
        loadTakenSlotsForDate(selectedDate);
      }
    } finally {
      submitting = false;
    }
  }

  function cerrarConfirm() {
    if (!submitting) {
      showConfirmModal = false;
      selectedDate = null;
    }
  }

  function cerrarDetalle() {
    if (!submittingCancel && !submittingEdit) {
      showDetalleModal = false;
      showCancelConfirmModal = false;
      selectedVisit = null;
      editMode = false;
      detalleError = null;
    }
  }

  function getVisitSlotLabel(visit) {
    const time = visit?.scheduled_time || '';
    return HORARIOS.find((h) => h.value === time)?.label || time;
  }

  async function cancelarVisita() {
    if (!selectedVisit) return;
    submittingCancel = true;
    detalleError = null;
    try {
      await scheduledVisitsAPI.update(selectedVisit.id, { status: 'cancelada' });
      await cargarMisVisitas();
      const range = getMonthRange(new Date(calendar?.getCurrentData()?.currentDate || new Date()));
      await fetchAvailability(range.from, range.to);
      showCancelConfirmModal = false;
      cerrarDetalle();
      successMessage = 'Visita cancelada.';
    } catch (e) {
      detalleError = e.message || 'Error al cancelar.';
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

  async function loadTakenSlotsForEdit() {
    if (!editDate) return;
    loadingEditSlots = true;
    takenSlotsForEdit = [];
    try {
      const res = await scheduledVisitsAPI.getAvailability(editDate, editDate);
      if (res.success && res.data?.occupied_slots_by_date?.[editDate]) {
        let slots = res.data.occupied_slots_by_date[editDate];
        if (selectedVisit?.scheduled_time) {
          slots = slots.filter((s) => s !== selectedVisit.scheduled_time);
        }
        takenSlotsForEdit = slots;
      }
    } catch (e) {
      takenSlotsForEdit = [];
    } finally {
      loadingEditSlots = false;
    }
  }

  function abrirEditar() {
    editMode = true;
    detalleError = null;
    loadTakenSlotsForEdit();
  }

  async function guardarCambiosVisita() {
    if (!selectedVisit || !editDate || !editTime) return;
    const slotTaken = takenSlotsForEdit.includes(editTime);
    if (slotTaken && editTime !== selectedVisit.scheduled_time) {
      detalleError = 'Ese horario ya está ocupado para esa fecha.';
      return;
    }
    submittingEdit = true;
    detalleError = null;
    try {
      await scheduledVisitsAPI.update(selectedVisit.id, {
        scheduled_date: editDate,
        scheduled_time: editTime,
      });
      await cargarMisVisitas();
      const range = getMonthRange(new Date(calendar?.getCurrentData()?.currentDate || new Date()));
      await fetchAvailability(range.from, range.to);
      editMode = false;
      selectedVisit = null;
      showDetalleModal = false;
      successMessage = 'Visita actualizada.';
    } catch (e) {
      detalleError = e.message || 'Error al actualizar.';
      if (e.message && e.message.includes('horario ya está ocupado')) {
        loadTakenSlotsForEdit();
      }
    } finally {
      submittingEdit = false;
    }
  }
</script>

<svelte:head>
  <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.min.css" rel="stylesheet" />
</svelte:head>

<div class="py-6">
  <div class="mb-4 sm:mb-6">
    <h2 class="text-xl sm:text-2xl font-semibold text-gray-700">Agendar visita del jardinero</h2>
    <p class="mt-1 text-sm sm:text-base text-gray-600">
      <strong>Elige un día disponible.</strong> En <strong style="color: #3788d8">azul</strong> aparecen tus visitas reservadas y en <strong style="color: rgb(217, 119, 6)">"Naranja"</strong> los días bloqueados (no se puede reservar).
    </p>
  </div>

  {#if error && !showConfirmModal}
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
      {error}
    </div>
  {/if}
  {#if successMessage}
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
      {successMessage}
    </div>
  {/if}

  {#if loading}
    <div class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      <p class="ml-4 text-gray-600">Cargando...</p>
    </div>
  {:else}
    <Card className="overflow-hidden relative">
      {#if loadingAvailability}
        <div class="absolute top-4 right-4 z-10 flex items-center gap-2 text-sm text-gray-500 bg-white/90 px-2 py-1 rounded">
          <span class="animate-spin rounded-full h-4 w-4 border-2 border-primary-500 border-t-transparent"></span>
          Actualizando...
        </div>
      {/if}
      <div class="p-2 sm:p-4 md:p-6 overflow-x-auto">
        <div bind:this={calendarEl} class="agendar-calendar min-w-[280px]"></div>
      </div>
    </Card>
  {/if}
</div>

<Modal
  isOpen={showConfirmModal}
  title="Confirmar visita"
  onClose={cerrarConfirm}
  size="sm"
>
  <div>
    {#if selectedDate}
      <p class="text-gray-700 mb-4">
        Reservar visita para el <strong>{formatFecha(selectedDate)}</strong>
      </p>
      {#if blockedDates.has(selectedDate)}
        <p class="text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-sm mb-3">
          Este día está bloqueado. No se pueden reservar turnos.
        </p>
      {/if}
      <label for="confirm-time-select" class="block text-sm font-medium text-gray-700 mb-2">Horario (2 h)</label>
      {#if loadingSlots}
        <p class="text-sm text-gray-500 py-2">Cargando horarios disponibles...</p>
      {:else}
        <select
          id="confirm-time-select"
          bind:value={selectedTime}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
          {#each HORARIOS as h}
            <option
              value={h.value}
              disabled={takenSlotsForDate.includes(h.value)}
            >
              {h.label}{takenSlotsForDate.includes(h.value) ? ' (ocupado)' : ''}
            </option>
          {/each}
        </select>
        {#if takenSlotsForDate.length === HORARIOS.length}
          <p class="mt-2 text-sm text-amber-600">No hay horarios libres ese día. Elegí otra fecha.</p>
        {/if}
      {/if}
      {#if error && showConfirmModal}
        <p class="mt-3 text-sm text-red-600">{error}</p>
      {/if}
    {/if}
  </div>
  <div slot="footer" class="flex justify-end gap-3">
    <button
      type="button"
      on:click={cerrarConfirm}
      disabled={submitting}
      class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50"
    >
      Cancelar
    </button>
    <button
      type="button"
      on:click={confirmarReserva}
      disabled={submitting || loadingSlots || (selectedDate && takenSlotsForDate.length === HORARIOS.length) || (selectedDate && blockedDates.has(selectedDate))}
      class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2"
    >
      {#if submitting}
        <span class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
      {/if}
      Reservar
    </button>
  </div>
</Modal>

<!-- Modal detalle de mi visita: ver, cancelar o editar -->
<Modal
  isOpen={showDetalleModal}
  title={selectedVisit?.status === 'cancelada' ? 'Visita cancelada' : 'Tu visita reservada'}
  onClose={cerrarDetalle}
  size="sm"
>
  <div>
    {#if selectedVisit}
      {#if selectedVisit.status === 'cancelada'}
        <p class="text-gray-700 mb-2">
          <strong>Fecha:</strong> {formatFecha(String(selectedVisit.scheduled_date).slice(0, 10))}
        </p>
        <p class="text-gray-700 mb-4">
          <strong>Horario:</strong> {getVisitSlotLabel(selectedVisit)}
        </p>
        <p class="text-red-600 font-medium mb-4">Esta visita fue cancelada.</p>
        <button
          type="button"
          on:click={cerrarDetalle}
          class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
        >
          Cerrar
        </button>
      {:else if !editMode}
        <p class="text-gray-700 mb-2">
          <strong>Fecha:</strong> {formatFecha(String(selectedVisit.scheduled_date).slice(0, 10))}
        </p>
        <p class="text-gray-700 mb-4">
          <strong>Horario:</strong> {getVisitSlotLabel(selectedVisit)}
        </p>
        {#if detalleError}
          <p class="text-sm text-red-600 mb-3">{detalleError}</p>
        {/if}
        <div class="flex flex-col sm:flex-row gap-2">
          <button
            type="button"
            on:click={abrirEditar}
            disabled={submittingCancel}
            class="px-4 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors"
          >
            Cambiar fecha u horario
          </button>
          <button
            type="button"
            on:click={abrirConfirmCancelar}
            disabled={submittingCancel}
            class="px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center gap-2"
          >
            Cancelar visita
          </button>
        </div>
      {:else}
        <p class="text-sm text-gray-600 mb-3">Elegí la nueva fecha y horario:</p>
        <label for="edit-date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
        <input
          id="edit-date"
          type="date"
          bind:value={editDate}
          on:change={() => loadTakenSlotsForEdit()}
          class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3"
        />
        <label for="edit-time" class="block text-sm font-medium text-gray-700 mb-1">Horario</label>
        {#if loadingEditSlots}
          <p class="text-sm text-gray-500 py-2">Cargando horarios...</p>
        {:else}
          <select
            id="edit-time"
            bind:value={editTime}
            class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3"
          >
            {#each HORARIOS as h}
              <option
                value={h.value}
                disabled={takenSlotsForEdit.includes(h.value)}
              >
                {h.label}{takenSlotsForEdit.includes(h.value) ? ' (ocupado)' : ''}
              </option>
            {/each}
          </select>
        {/if}
        {#if detalleError}
          <p class="text-sm text-red-600 mb-3">{detalleError}</p>
        {/if}
        <div class="flex gap-2">
          <button
            type="button"
            on:click={() => { editMode = false; detalleError = null; }}
            disabled={submittingEdit}
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Volver
          </button>
          <button
            type="button"
            on:click={guardarCambiosVisita}
            disabled={submittingEdit || loadingEditSlots}
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 flex items-center gap-2"
          >
            {#if submittingEdit}
              <span class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
            {/if}
            Guardar cambios
          </button>
        </div>
      {/if}
    {/if}
  </div>
</Modal>

<!-- Modal confirmar cancelación -->
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

<style>
  :global(.agendar-calendar) {
    --fc-border-color: #e5e7eb;
    --fc-button-bg-color: #2563eb;
    --fc-button-border-color: #2563eb;
    --fc-button-hover-bg-color: #1d4ed8;
    --fc-button-hover-border-color: #1d4ed8;
    --fc-today-bg-color: #eff6ff;
  }
  :global(.agendar-calendar .fc-day-occupied) {
    background-color: #f3f4f6 !important;
    cursor: not-allowed;
    opacity: 0.8;
  }
  :global(.agendar-calendar .fc-day-occupied .fc-daygrid-day-number) {
    color: #9ca3af;
  }
  /* Día bloqueado por admin: celda entera en amarillo (color que pediste) */
  :global(.agendar-calendar .fc-day-blocked),
  :global(.agendar-calendar .fc-day-blocked *),
  :global(.agendar-calendar td.fc-day-blocked),
  :global(.agendar-calendar td.fc-day-blocked *),
  :global(.agendar-calendar .fc-day-blocked .fc-scrollgrid-sync-inner),
  :global(.agendar-calendar .fc-day-blocked .fc-daygrid-day-frame),
  :global(.agendar-calendar .fc-day-blocked .fc-daygrid-day-events),
  :global(.agendar-calendar .fc-day-blocked .fc-daygrid-day-bg) {
    background-color: #d97706 !important;
    background-image: none !important;
    cursor: not-allowed !important;
  }
  :global(.agendar-calendar .fc-day-blocked .fc-daygrid-day-number) {
    color: #fff !important;
    font-weight: 700;
  }
  :global(.agendar-calendar .fc-day-blocked .fc-daygrid-day-frame) {
    position: relative;
    min-height: 100%;
  }
  :global(.agendar-calendar .fc-day-blocked .fc-daygrid-day-frame::after) {
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
  :global(.fc-event-mi-visita) {
    background-color: #2563eb;
    border-color: #1d4ed8;
    color: #fff;
    cursor: pointer;
  }
  :global(.fc-event-cancelada) {
    background-color: #dc2626 !important;
    border-color: #b91c1c !important;
    opacity: 0.95;
  }
  /* Mobile-friendly */
  @media (max-width: 640px) {
    :global(.agendar-calendar .fc-toolbar) {
      flex-direction: column;
      gap: 0.5rem;
      padding: 0.25rem 0;
    }
    :global(.agendar-calendar .fc-toolbar-title) {
      font-size: 1rem;
      margin: 0;
    }
    :global(.agendar-calendar .fc-button) {
      padding: 0.5rem 0.75rem;
      font-size: 0.875rem;
      min-height: 44px;
      min-width: 44px;
    }
    :global(.agendar-calendar .fc-scrollgrid) {
      font-size: 0.75rem;
    }
    :global(.agendar-calendar .fc-col-header-cell-cushion),
    :global(.agendar-calendar .fc-daygrid-day-number) {
      padding: 0.35rem;
      font-size: 0.8rem;
    }
    :global(.agendar-calendar .fc-daygrid-day) {
      min-height: 44px;
    }
    :global(.agendar-calendar) {
      padding: 0.25rem !important;
    }
  }
</style>
