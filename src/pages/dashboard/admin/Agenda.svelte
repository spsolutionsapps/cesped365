<script>
  import { onMount } from 'svelte';
  import { slide } from 'svelte/transition';
  import { scheduledVisitsAPI } from '../../../services/api';
  import Card from '../../../components/Card.svelte';
  import Badge from '../../../components/Badge.svelte';
  import AgendarVisitaModal from '../../../components/AgendarVisitaModal.svelte';
  import ReagendarVisitaModal from '../../../components/ReagendarVisitaModal.svelte';
  
  let visitas = [];
  let loading = true;
  let error = null;
  let showAgendarModal = false;
  let filtroFecha = '';
  let filtroEstado = 'programada';
  let visitasAbiertas = new Set(); // IDs de visitas expandidas
  let filtrosAbiertos = false; // Estado de filtros colapsables
  
  // Estado del modal de reagendar
  let visitaSeleccionada = null;
  let showReagendarModal = false;
  
  onMount(async () => {
    await cargarVisitas();
  });
  
  async function cargarVisitas() {
    try {
      loading = true;
      const response = await scheduledVisitsAPI.getAll();
      if (response.success) {
        visitas = response.data;
        // Ordenar por fecha
        visitas.sort((a, b) => new Date(a.scheduled_date) - new Date(b.scheduled_date));
      }
      loading = false;
    } catch (err) {
      console.error('Error cargando visitas:', err);
      error = 'Error al cargar las visitas programadas. Verifica que el backend esté corriendo.';
      loading = false;
    }
  }
  
  async function eliminarVisita(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta visita programada?')) {
      return;
    }

    try {
      const response = await scheduledVisitsAPI.delete(id);
      if (response.success) {
        await cargarVisitas();
      }
    } catch (err) {
      console.error('Error eliminando visita:', err);
      alert('Error al eliminar la visita');
    }
  }
  
  async function marcarCompletada(id) {
    try {
      loading = true;
      const response = await scheduledVisitsAPI.update(id, { status: 'completada' });
      if (response.success) {
        await cargarVisitas();
      } else {
        throw new Error(response.message || 'Error al actualizar la visita');
      }
    } catch (err) {
      console.error('Error actualizando visita:', err);
      error = err.message || 'Error al marcar la visita como completada';
      alert(error);
    } finally {
      loading = false;
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
    await cargarVisitas();
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
  
  $: visitasFiltradas = visitas.filter(visita => {
    const matchesEstado = filtroEstado === 'todas' || visita.status === filtroEstado;
    const matchesFecha = !filtroFecha || visita.scheduled_date.startsWith(filtroFecha);
    return matchesEstado && matchesFecha;
  });
  
  $: visitasHoy = visitasFiltradas.filter(v => esHoy(v.scheduled_date) && v.status === 'programada');
  $: visitasProximas = visitasFiltradas.filter(v => !esPasada(v.scheduled_date) && !esHoy(v.scheduled_date) && v.status === 'programada');
  $: visitasPasadas = visitasFiltradas.filter(v => esPasada(v.scheduled_date) || v.status !== 'programada');
  
  // Contar filtros activos
  $: filtrosActivos = (filtroFecha ? 1 : 0) + (filtroEstado !== 'programada' ? 1 : 0);
</script>

<div class="py-6">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <h2 class="text-2xl font-semibold text-gray-700">
      Agenda de Visitas
    </h2>
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

  <!-- Filtros Colapsables -->
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
    <div class="hidden md:block mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Filtros</h3>
    </div>
    
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

  {#if loading}
    <div class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      <p class="ml-4 text-gray-600">Cargando agenda...</p>
    </div>
  {:else}
    <!-- Visitas de hoy -->
    {#if visitasHoy.length > 0}
      <Card className="mb-6">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-2 h-2 bg-red-500 rounded-full"></div>
          <h3 class="text-lg font-semibold text-gray-900">Visitas de Hoy</h3>
          <Badge type="danger">{visitasHoy.length}</Badge>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          {#each visitasHoy as visita}
            <div class="bg-red-50 border border-red-200 rounded-lg overflow-hidden flex flex-col sm:shadow-md hover:shadow-lg transition-shadow">
              <!-- Header collapsable solo en mobile -->
              <button
                on:click={() => toggleVisita(visita.id)}
                class="w-full p-4 flex items-center justify-between hover:bg-red-100 transition-colors sm:pointer-events-none sm:hover:bg-transparent sm:p-4"
              >
                <div class="flex items-center gap-3 flex-1 min-w-0 sm:flex-col sm:items-start sm:gap-2">
                  <svg 
                    class="w-5 h-5 text-gray-600 flex-shrink-0 transition-transform {visitasAbiertas.has(visita.id) ? 'rotate-90' : ''} sm:hidden"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <div class="flex-1 min-w-0 sm:flex-1 sm:w-full">
                    <h4 class="font-semibold text-gray-900 truncate sm:text-base">{visita.cliente_nombre}</h4>
                    <p class="text-xs text-gray-600 mt-1 sm:text-sm">{formatearFechaCorta(visita.scheduled_date)}</p>
                  </div>
                  <Badge type={getBadgeType(visita.status)} class="sm:mt-2">
                    {getStatusLabel(visita.status)}
                  </Badge>
                </div>
              </button>
              
              <!-- Contenido: collapsable en mobile, siempre visible en desktop -->
              <div class="px-4 pb-4 border-t border-red-200 sm:block sm:flex-1 sm:flex sm:flex-col {visitasAbiertas.has(visita.id) ? 'block' : 'hidden'}">
                <div class="pt-4 space-y-2 sm:flex-1">
                  <p class="text-sm text-gray-700">
                    <strong>Dirección:</strong> <span class="block sm:inline">{visita.direccion}</span>
                  </p>
                  <p class="text-sm text-gray-700">
                    <strong>Fecha:</strong> {formatearFecha(visita.scheduled_date)}
                  </p>
                  {#if visita.scheduled_time}
                    <p class="text-sm text-gray-700">
                      <strong>Hora:</strong> {visita.scheduled_time}
                    </p>
                  {/if}
                  {#if visita.gardener_name}
                    <p class="text-sm text-gray-700">
                      <strong>Jardinero:</strong> {visita.gardener_name}
                    </p>
                  {/if}
                  {#if visita.notes}
                    <p class="text-sm text-gray-600 mt-2 italic">"{visita.notes}"</p>
                  {/if}
                </div>
                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-red-200 sm:mt-auto">
                  {#if visita.status === 'programada'}
                    <button
                      on:click={() => abrirReagendarModal(visita)}
                      disabled={loading}
                      class="px-3 py-1.5 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 flex-1 sm:flex-none justify-center"
                      title="Postergar por lluvia"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                      </svg>
                      Postergar por lluvia
                    </button>
                    <button
                      on:click={() => marcarCompletada(visita.id)}
                      disabled={loading}
                      class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 flex-1 sm:flex-none justify-center"
                      title="Marcar como completada"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Completar
                    </button>
                  {/if}
                  <button
                    on:click={() => eliminarVisita(visita.id)}
                    disabled={loading}
                    class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 flex-1 sm:flex-none justify-center"
                    title="Eliminar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                  </button>
                </div>
              </div>
            </div>
          {/each}
        </div>
      </Card>
    {/if}

    <!-- Próximas visitas -->
    {#if visitasProximas.length > 0}
      <Card className="mb-6">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
          <h3 class="text-lg font-semibold text-gray-900">Próximas Visitas</h3>
          <Badge type="info">{visitasProximas.length}</Badge>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          {#each visitasProximas as visita}
            <div class="bg-blue-50 border border-blue-200 rounded-lg overflow-hidden flex flex-col sm:shadow-md hover:shadow-lg transition-shadow">
              <!-- Header collapsable solo en mobile -->
              <button
                on:click={() => toggleVisita(visita.id)}
                class="w-full p-4 flex items-center justify-between hover:bg-blue-100 transition-colors sm:pointer-events-none sm:hover:bg-transparent sm:p-4"
              >
                <div class="flex items-center gap-3 flex-1 min-w-0 sm:flex-col sm:items-start sm:gap-2">
                  <svg 
                    class="w-5 h-5 text-gray-600 flex-shrink-0 transition-transform {visitasAbiertas.has(visita.id) ? 'rotate-90' : ''} sm:hidden"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <div class="flex-1 min-w-0 sm:flex-1 sm:w-full">
                    <h4 class="font-semibold text-gray-900 truncate sm:text-base">{visita.cliente_nombre}</h4>
                    <p class="text-xs text-gray-600 mt-1 sm:text-sm">{formatearFechaCorta(visita.scheduled_date)}</p>
                  </div>
                  <Badge type={getBadgeType(visita.status)} class="sm:mt-2">
                    {getStatusLabel(visita.status)}
                  </Badge>
                </div>
              </button>
              
              <!-- Contenido: collapsable en mobile, siempre visible en desktop -->
              <div class="px-4 pb-4 border-t border-blue-200 sm:block sm:flex-1 sm:flex sm:flex-col {visitasAbiertas.has(visita.id) ? 'block' : 'hidden'}">
                <div class="pt-4 space-y-2 sm:flex-1">
                  <p class="text-sm text-gray-700">
                    <strong>Dirección:</strong> <span class="block sm:inline">{visita.direccion}</span>
                  </p>
                  <p class="text-sm text-gray-700">
                    <strong>Fecha:</strong> {formatearFecha(visita.scheduled_date)}
                  </p>
                  {#if visita.scheduled_time}
                    <p class="text-sm text-gray-700">
                      <strong>Hora:</strong> {visita.scheduled_time}
                    </p>
                  {/if}
                  {#if visita.gardener_name}
                    <p class="text-sm text-gray-700">
                      <strong>Jardinero:</strong> {visita.gardener_name}
                    </p>
                  {/if}
                  {#if visita.notes}
                    <p class="text-sm text-gray-600 mt-2 italic">"{visita.notes}"</p>
                  {/if}
                </div>
                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-blue-200 sm:mt-auto">
                  {#if visita.status === 'programada'}
                    <button
                      on:click={() => abrirReagendarModal(visita)}
                      disabled={loading}
                      class="px-3 py-1.5 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 flex-1 sm:flex-none justify-center"
                      title="Postergar por lluvia"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                      </svg>
                      Postergar por lluvia
                    </button>
                    <button
                      on:click={() => marcarCompletada(visita.id)}
                      disabled={loading}
                      class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 flex-1 sm:flex-none justify-center"
                      title="Marcar como completada"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Completar
                    </button>
                  {/if}
                  <button
                    on:click={() => eliminarVisita(visita.id)}
                    disabled={loading}
                    class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 flex-1 sm:flex-none justify-center"
                    title="Eliminar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                  </button>
                </div>
              </div>
            </div>
          {/each}
        </div>
      </Card>
    {/if}

    <!-- Visitas pasadas/completadas -->
    {#if visitasPasadas.length > 0}
      <Card>
        <div class="flex items-center gap-2 mb-4">
          <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
          <h3 class="text-lg font-semibold text-gray-900">Historial</h3>
          <Badge type="default">{visitasPasadas.length}</Badge>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          {#each visitasPasadas as visita}
            <div class="bg-gray-50 border border-gray-200 rounded-lg overflow-hidden flex flex-col sm:shadow-md hover:shadow-lg transition-shadow">
              <!-- Header collapsable solo en mobile -->
              <button
                on:click={() => toggleVisita(visita.id)}
                class="w-full p-4 flex items-center justify-between hover:bg-gray-100 transition-colors sm:pointer-events-none sm:hover:bg-transparent sm:p-4"
              >
                <div class="flex items-center gap-3 flex-1 min-w-0 sm:flex-col sm:items-start sm:gap-2">
                  <svg 
                    class="w-5 h-5 text-gray-600 flex-shrink-0 transition-transform {visitasAbiertas.has(visita.id) ? 'rotate-90' : ''} sm:hidden"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <div class="flex-1 min-w-0 sm:flex-1 sm:w-full">
                    <h4 class="font-semibold text-gray-900 truncate sm:text-base">{visita.cliente_nombre}</h4>
                    <p class="text-xs text-gray-600 mt-1 sm:text-sm">{formatearFechaCorta(visita.scheduled_date)}</p>
                  </div>
                  <Badge type={getBadgeType(visita.status)} class="sm:mt-2">
                    {getStatusLabel(visita.status)}
                  </Badge>
                </div>
              </button>
              
              <!-- Contenido: collapsable en mobile, siempre visible en desktop -->
              <div class="px-4 pb-4 border-t border-gray-200 sm:block sm:flex-1 sm:flex sm:flex-col {visitasAbiertas.has(visita.id) ? 'block' : 'hidden'}">
                <div class="pt-4 space-y-2 sm:flex-1">
                  <p class="text-sm text-gray-700">
                    <strong>Dirección:</strong> <span class="block sm:inline">{visita.direccion}</span>
                  </p>
                  <p class="text-sm text-gray-700">
                    <strong>Fecha:</strong> {formatearFecha(visita.scheduled_date)}
                  </p>
                  {#if visita.scheduled_time}
                    <p class="text-sm text-gray-700">
                      <strong>Hora:</strong> {visita.scheduled_time}
                    </p>
                  {/if}
                  {#if visita.gardener_name}
                    <p class="text-sm text-gray-700">
                      <strong>Jardinero:</strong> {visita.gardener_name}
                    </p>
                  {/if}
                  {#if visita.notes}
                    <p class="text-sm text-gray-600 mt-2 italic">"{visita.notes}"</p>
                  {/if}
                </div>
                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-200 sm:mt-auto">
                  <button
                    on:click={() => eliminarVisita(visita.id)}
                    disabled={loading}
                    class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 flex-1 sm:flex-none justify-center"
                    title="Eliminar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                  </button>
                </div>
              </div>
            </div>
          {/each}
        </div>
      </Card>
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
</div>

<!-- Modal Agendar Visita -->
<AgendarVisitaModal
  isOpen={showAgendarModal}
  onClose={() => showAgendarModal = false}
  onSuccess={async () => {
    showAgendarModal = false;
    await cargarVisitas();
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
