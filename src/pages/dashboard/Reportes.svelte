<script>
  import { onMount } from 'svelte';
  import { slide } from 'svelte/transition';
  import { reportesAPI, jardinesAPI } from '../../services/api';
  import { auth } from '../../stores/auth';
  import Card from '../../components/Card.svelte';
  import Badge from '../../components/Badge.svelte';
  import CrearReporteModal from '../../components/CrearReporteModal.svelte';

  import ImageGalleryModal from '../../components/ImageGalleryModal.svelte';
  
  let reportes = [];
  let reportesFiltrados = [];
  let selectedReporte = null;
  let loading = true;
  let error = null;
  let showCrearModal = false;

  let reporteParaEditar = null;
  let userRole;
  let jardines = [];
  let showImageGallery = false;
  let galleryImages = [];
  let galleryInitialIndex = 0;
  
  // Filtros y paginación
  let busqueda = '';
  let filtroJardin = '';
  let filtroFechaDesde = '';
  let filtroFechaHasta = '';
  let paginaActual = 1;
  let reportesPorPagina = 9;
  
  // Vista: 'cards' o 'tabla'
  let vista = 'cards';
  
  // Estado de filtros colapsables
  let filtrosAbiertos = false;
  
  auth.subscribe(value => {
    userRole = value.role;
  });
  
  onMount(async () => {
    await Promise.all([cargarReportes(), cargarJardines()]);
  });

  async function cargarReportes() {
    try {
      loading = true;
      const response = await reportesAPI.getAll();
      if (response.success) {
        reportes = response.data;
        aplicarFiltros();
      }
      loading = false;
    } catch (err) {
      console.error('Error cargando reportes:', err);
      error = 'Error al cargar los reportes. Verifica que el backend esté corriendo.';
      loading = false;
    }
  }

  async function cargarJardines() {
    try {
      const response = await jardinesAPI.getAll();
      if (response.success) {
        jardines = response.data;
      }
    } catch (err) {
      console.error('Error cargando jardines:', err);
    }
  }

  function aplicarFiltros() {
    let filtrados = [...reportes];

    // Filtro por jardín
    if (filtroJardin) {
      filtrados = filtrados.filter(r => r.garden_id == filtroJardin);
    }

    // Filtro por fecha desde
    if (filtroFechaDesde) {
      filtrados = filtrados.filter(r => r.fecha >= filtroFechaDesde);
    }

    // Filtro por fecha hasta
    if (filtroFechaHasta) {
      filtrados = filtrados.filter(r => r.fecha <= filtroFechaHasta);
    }

    // Búsqueda por jardinero u observaciones
    if (busqueda) {
      const termino = busqueda.toLowerCase();
      filtrados = filtrados.filter(r => 
        (r.jardinero && r.jardinero.toLowerCase().includes(termino)) ||
        (r.observaciones && r.observaciones.toLowerCase().includes(termino)) ||
        (r.direccion && r.direccion.toLowerCase().includes(termino))
      );
    }

    reportesFiltrados = filtrados;
    paginaActual = 1; // Reset a primera página
  }

  $: if (busqueda !== undefined || filtroJardin !== undefined || filtroFechaDesde !== undefined || filtroFechaHasta !== undefined) {
    aplicarFiltros();
  }
  
  // Resetear página al cambiar de vista
  $: if (vista !== undefined) {
    paginaActual = 1;
  }

  // Paginación - ajustar items por página según vista
  $: reportesPorPaginaAjustado = vista === 'tabla' ? 15 : 9;
  $: reportesPaginados = reportesFiltrados.slice(
    (paginaActual - 1) * reportesPorPaginaAjustado,
    paginaActual * reportesPorPaginaAjustado
  );

  $: totalPaginas = Math.ceil(reportesFiltrados.length / reportesPorPaginaAjustado);
  
  function getBadgeType(estado) {
    if (estado === 'Bueno') return 'success';
    if (estado === 'Regular') return 'warning';
    return 'danger';
  }
  
  function selectReporte(reporte) {
    selectedReporte = reporte;
  }
  
  function closeModal() {
    selectedReporte = null;
  }

  function openImageGallery(images, index = 0) {
    galleryImages = images;
    galleryInitialIndex = index;
    showImageGallery = true;
  }

  function closeImageGallery() {
    showImageGallery = false;
    galleryImages = [];
    galleryInitialIndex = 0;
  }

  async function handleReporteCreado() {
    await cargarReportes();
  }
  
  function editarReporte(reporte) {
    reporteParaEditar = reporte;
    showCrearModal = true;
  }
  
  function handleModalClose() {
    showCrearModal = false;
    reporteParaEditar = null;
  }

  async function eliminarReporte(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este reporte? Esta acción no se puede deshacer.')) {
      return;
    }

    try {
      const response = await reportesAPI.delete(id);
      if (response.success) {
        await cargarReportes();
      }
    } catch (err) {
      console.error('Error eliminando reporte:', err);
      alert('Error al eliminar el reporte');
    }
  }

  function limpiarFiltros() {
    busqueda = '';
    filtroJardin = '';
    filtroFechaDesde = '';
    filtroFechaHasta = '';
  }
  
  // Contar filtros activos
  $: filtrosActivos = (busqueda ? 1 : 0) + 
                      (filtroJardin ? 1 : 0) + 
                      (filtroFechaDesde ? 1 : 0) + 
                      (filtroFechaHasta ? 1 : 0);
</script>

<div class="py-6">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-gray-700">
      Reportes de Jardín
    </h2>
    
    <div class="flex items-center gap-4">
      <!-- Selector de vista -->
      {#if !loading && reportesFiltrados.length > 0}
        <div class="flex items-center gap-2 bg-gray-100 rounded-lg p-1">
          <button
            on:click={() => vista = 'cards'}
            class="px-4 py-2 rounded-md text-sm font-medium transition-colors {vista === 'cards' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'}"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
              Tarjetas
            </div>
          </button>
          <button
            on:click={() => vista = 'tabla'}
            class="px-4 py-2 rounded-md text-sm font-medium transition-colors {vista === 'tabla' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'}"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              Tabla
            </div>
          </button>
        </div>
      {/if}
      
      {#if userRole === 'admin'}
        <button
          on:click={() => showCrearModal = true}
          class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Crear Nuevo Reporte
        </button>
      {/if}
    </div>
  </div>

  {#if error}
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
      {error}
    </div>
  {/if}

  <!-- Filtros de Búsqueda Colapsables -->
  <Card>
    <button
      on:click={() => filtrosAbiertos = !filtrosAbiertos}
      class="w-full flex items-center justify-between rounded-lg transition-colors"
    >
      <div class="flex items-center gap-3">
        <h3 class="text-lg font-semibold text-gray-900">Filtros de Búsqueda</h3>
        {#if filtrosActivos > 0}
          <Badge type="info">{filtrosActivos} activo{filtrosActivos > 1 ? 's' : ''}</Badge>
        {/if}
      </div>
      <div class="flex items-center gap-2">
        <!-- Contador de resultados -->
        <span class="text-sm text-gray-600">
          {reportesPaginados.length} de {reportesFiltrados.length} reportes
        </span>
        <svg
          class="w-5 h-5 text-gray-500 transition-transform {filtrosAbiertos ? 'rotate-180' : ''}"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </button>
    
    {#if filtrosAbiertos}
      <div transition:slide>
        <div class="pb-4 space-y-4 pt-4">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Búsqueda por texto -->
            <div>
              <label for="busqueda" class="block text-sm font-medium text-gray-700 mb-2">
                Buscar
              </label>
              <input
                id="busqueda"
                type="text"
                bind:value={busqueda}
                placeholder="Jardinero u observaciones..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
              />
            </div>

            <!-- Filtro por jardín -->
            <div>
              <label for="filtro-jardin" class="block text-sm font-medium text-gray-700 mb-2">
                Jardín
              </label>
              <select
                id="filtro-jardin"
                bind:value={filtroJardin}
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
              >
                <option value="">Todos los jardines</option>
                {#each jardines as jardin}
                  <option value={jardin.id}>{jardin.address}</option>
                {/each}
              </select>
            </div>

            <!-- Fecha desde -->
            <div>
              <label for="fecha-desde" class="block text-sm font-medium text-gray-700 mb-2">
                Desde
              </label>
              <input
                id="fecha-desde"
                type="date"
                bind:value={filtroFechaDesde}
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
              />
            </div>

            <!-- Fecha hasta -->
            <div>
              <label for="fecha-hasta" class="block text-sm font-medium text-gray-700 mb-2">
                Hasta
              </label>
              <input
                id="fecha-hasta"
                type="date"
                bind:value={filtroFechaHasta}
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
              />
            </div>
          </div>

          <!-- Botón limpiar filtros -->
          {#if filtrosActivos > 0}
            <button
              on:click={limpiarFiltros}
              class="text-sm text-primary-600 hover:text-primary-700 font-medium"
            >
              Limpiar filtros
            </button>
          {/if}
        </div>
      </div>
    {/if}
  </Card>

  {#if loading}
    <div class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      <p class="ml-4 text-gray-600">Cargando reportes...</p>
    </div>
  {:else if reportesFiltrados.length === 0}
    <div class="text-center py-12 rounded-lg">
      <p class="text-gray-600">No se encontraron reportes con los filtros aplicados</p>
    </div>
  {:else}
    {#if vista === 'cards'}
      <!-- Vista de tarjetas -->
      <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3 mt-6">
        {#each reportesPaginados as reporte}
      <Card>
        <div class="space-y-4">
          <!-- Header del reporte -->
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-gray-500">
                {new Date(reporte.fecha).toLocaleDateString('es-AR', { 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                })}
              </p>
              <p class="text-xs text-gray-400 mt-1">por {reporte.jardinero}</p>
            </div>
            <Badge type={getBadgeType(reporte.estadoGeneral)}>
              {reporte.estadoGeneral}
            </Badge>
          </div>

          <!-- Indicadores visuales -->
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="flex items-center">
              {#if reporte.cespedParejo}
                <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              {:else}
                <svg class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              {/if}
              <span class="text-gray-600">Parejo</span>
            </div>
            <div class="flex items-center">
              {#if reporte.colorOk}
                <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              {:else}
                <svg class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              {/if}
              <span class="text-gray-600">Color</span>
            </div>
            <div class="flex items-center">
              {#if !reporte.manchas}
                <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              {:else}
                <svg class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              {/if}
              <span class="text-gray-600">Manchas</span>
            </div>
            <div class="flex items-center">
              {#if !reporte.malezasVisibles}
                <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              {:else}
                <svg class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              {/if}
              <span class="text-gray-600">Malezas</span>
            </div>
          </div>

          <!-- Nota resumida -->
          <p class="text-sm text-gray-600 line-clamp-3">
            {reporte.notaJardinero}
          </p>

          <!-- Crecimiento -->
          <div class="flex items-center text-sm">
            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <span class="text-gray-600">Crecimiento: <strong>{reporte.crecimientoCm} cm</strong></span>
          </div>

          <!-- Botones de acción -->
          <div class="flex gap-2 mt-4">
            <button
              on:click={() => selectReporte(reporte)}
              class="flex-1 bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 font-medium text-sm"
            >
              Ver detalle
            </button>
            {#if userRole === 'admin'}
              <button
                on:click={() => editarReporte(reporte)}
                class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                title="Editar reporte"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button
                on:click={() => eliminarReporte(reporte.id)}
                class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                title="Eliminar reporte"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            {/if}
          </div>
        </div>
      </Card>
      {/each}
      </div>
    {:else}
      <!-- Vista de tabla -->
      <div class="mt-6">
        <Card>
          <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Fecha
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Jardinero
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Evaluación
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Crecimiento
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Observaciones
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              {#each reportesPaginados as reporte}
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {new Date(reporte.fecha).toLocaleDateString('es-AR', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                      })}
                    </div>
                    <div class="text-xs text-gray-500">
                      {new Date(reporte.fecha).toLocaleTimeString('es-AR', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                      })}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{reporte.jardinero || 'N/A'}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge type={getBadgeType(reporte.estadoGeneral)}>
                      {reporte.estadoGeneral}
                    </Badge>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-2">
                      <div class="flex items-center" title="Césped parejo">
                        {#if reporte.cespedParejo}
                          <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                          </svg>
                        {:else}
                          <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                          </svg>
                        {/if}
                      </div>
                      <div class="flex items-center" title="Color saludable">
                        {#if reporte.colorOk}
                          <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                          </svg>
                        {:else}
                          <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                          </svg>
                        {/if}
                      </div>
                      <div class="flex items-center" title="Sin manchas">
                        {#if !reporte.manchas}
                          <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                          </svg>
                        {:else}
                          <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                          </svg>
                        {/if}
                      </div>
                      <div class="flex items-center" title="Sin malezas">
                        {#if !reporte.malezasVisibles}
                          <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                          </svg>
                        {:else}
                          <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                          </svg>
                        {/if}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 font-medium">{reporte.crecimientoCm} cm</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-600 max-w-xs truncate" title={reporte.notaJardinero || reporte.observaciones || 'Sin observaciones'}>
                      {reporte.notaJardinero || reporte.observaciones || '-'}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center gap-2">
                      <button
                        on:click={() => selectReporte(reporte)}
                        class="text-primary-600 hover:text-primary-900 transition-colors"
                        title="Ver detalle"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                      {#if userRole === 'admin'}
                        <button
                          on:click={() => editarReporte(reporte)}
                          class="text-blue-600 hover:text-blue-900 transition-colors"
                          title="Editar reporte"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>
                        <button
                          on:click={() => eliminarReporte(reporte.id)}
                          class="text-red-600 hover:text-red-900 transition-colors"
                          title="Eliminar reporte"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      {/if}
                    </div>
                  </td>
                </tr>
              {/each}
            </tbody>
          </table>
          </div>
        </Card>
      </div>
    {/if}

    <!-- Paginación -->
    {#if totalPaginas > 1}
      <div class="flex items-center justify-between mt-6">
        <div class="text-sm text-gray-700">
          Página {paginaActual} de {totalPaginas}
        </div>
        <div class="flex gap-2">
          <button
            on:click={() => paginaActual = Math.max(1, paginaActual - 1)}
            disabled={paginaActual === 1}
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Anterior
          </button>
          
          <!-- Números de página -->
          {#each Array(totalPaginas) as _, i}
            {#if i + 1 === 1 || i + 1 === totalPaginas || (i + 1 >= paginaActual - 1 && i + 1 <= paginaActual + 1)}
              <button
                on:click={() => paginaActual = i + 1}
                class="px-4 py-2 text-sm font-medium rounded-lg {paginaActual === i + 1 ? 'bg-primary-600 text-white' : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'}"
              >
                {i + 1}
              </button>
            {:else if i + 1 === paginaActual - 2 || i + 1 === paginaActual + 2}
              <span class="px-2 py-2 text-gray-500">...</span>
            {/if}
          {/each}

          <button
            on:click={() => paginaActual = Math.min(totalPaginas, paginaActual + 1)}
            disabled={paginaActual === totalPaginas}
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Siguiente
          </button>
        </div>
      </div>
    {/if}
  {/if}
</div>

<!-- Modal de detalle -->
{#if selectedReporte}
  <!-- svelte-ignore a11y-click-events-have-key-events -->
  <!-- svelte-ignore a11y-no-noninteractive-element-interactions -->
  <div class="fixed inset-0 z-50 overflow-y-auto" on:click={closeModal} on:keydown={(e) => e.key === 'Escape' && closeModal()} role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <!-- svelte-ignore a11y-click-events-have-key-events -->
      <!-- svelte-ignore a11y-no-noninteractive-element-interactions -->
      <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" on:click={closeModal} on:keydown={(e) => e.key === 'Escape' && closeModal()} role="button" tabindex="0" aria-label="Cerrar modal"></div>

      <!-- Modal panel -->
      <!-- svelte-ignore a11y-no-noninteractive-element-interactions -->
      <div
        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
        on:click|stopPropagation={() => {}}
        role="document"
      >
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <!-- Header -->
          <div class="flex justify-between items-start mb-6">
            <div>
              <h3 class="text-2xl font-bold text-gray-900">Reporte de Jardín</h3>
              <p class="text-sm text-gray-500 mt-1">
                {new Date(selectedReporte.fecha).toLocaleDateString('es-AR', { 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                })}
              </p>
            </div>
            <button
              on:click={closeModal}
              class="text-gray-400 hover:text-gray-500"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Content -->
          <div class="space-y-6">
            <!-- Estado y jardinero -->
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-600">Jardinero</p>
                <p class="text-lg font-semibold text-gray-900">{selectedReporte.jardinero}</p>
              </div>
              <Badge type={getBadgeType(selectedReporte.estadoGeneral)}>
                {selectedReporte.estadoGeneral}
              </Badge>
            </div>

            <!-- Detalles técnicos -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-3">Evaluación Técnica</h4>
              <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center">
                  {#if selectedReporte.cespedParejo}
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  {:else}
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  {/if}
                  <span class="text-gray-700">Césped parejo</span>
                </div>
                <div class="flex items-center">
                  {#if selectedReporte.colorOk}
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  {:else}
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  {/if}
                  <span class="text-gray-700">Color saludable</span>
                </div>
                <div class="flex items-center">
                  {#if !selectedReporte.manchas}
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  {:else}
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  {/if}
                  <span class="text-gray-700">Sin manchas</span>
                </div>
                <div class="flex items-center">
                  {#if !selectedReporte.zonasDesgastadas}
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  {:else}
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  {/if}
                  <span class="text-gray-700">Sin zonas desgastadas</span>
                </div>
                <div class="flex items-center">
                  {#if !selectedReporte.malezasVisibles}
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  {:else}
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  {/if}
                  <span class="text-gray-700">Sin malezas visibles</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                  </svg>
                  <span class="text-gray-700">Crecimiento: <strong>{selectedReporte.crecimientoCm} cm</strong></span>
                </div>
              </div>
            </div>

            <!-- Plagas Detectadas -->
            {#if selectedReporte.plagas}
              <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="font-semibold text-red-900 mb-2">⚠️ Plagas Detectadas</h4>
                <p class="text-red-700">
                  {selectedReporte.pest_description || 'Sin descripción'}
                </p>
              </div>
            {/if}

            <!-- Fertilizante Aplicado -->
            {#if selectedReporte.fertilizer_applied}
              <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="font-semibold text-green-900 mb-2">✅ Fertilizante Aplicado</h4>
                <p class="text-green-700">
                  {selectedReporte.fertilizer_type || 'Tipo no especificado'}
                </p>
              </div>
            {/if}

            <!-- Trabajo Realizado -->
            {#if selectedReporte.work_done}
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">🔧 Trabajo Realizado</h4>
                <p class="text-gray-700 bg-gray-50 rounded-lg p-4">
                  {selectedReporte.work_done}
                </p>
              </div>
            {/if}

            <!-- Recomendaciones -->
            {#if selectedReporte.observaciones}
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">💡 Recomendaciones</h4>
                <p class="text-gray-700 bg-blue-50 rounded-lg p-4">
                  {selectedReporte.observaciones}
                </p>
              </div>
            {/if}

            <!-- Observaciones del Jardinero -->
            {#if selectedReporte.notaJardinero}
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">📝 Observaciones del Jardinero</h4>
                <p class="text-gray-700 bg-gray-50 rounded-lg p-4">
                  {selectedReporte.notaJardinero}
                </p>
              </div>
            {/if}

            <!-- Imágenes -->
            {#if selectedReporte.imagenes && selectedReporte.imagenes.length > 0}
              <div>
                <h4 class="font-semibold text-gray-900 mb-3">Fotografías</h4>
                <div class="grid grid-cols-2 gap-4">
                  {#each selectedReporte.imagenes as imagen, index}
                    <button
                      type="button"
                      on:click={() => openImageGallery(selectedReporte.imagenes, index)}
                      class="w-full h-48 rounded-lg shadow-md hover:shadow-xl transition-shadow cursor-pointer overflow-hidden focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                      <img 
                        src={imagen} 
                        alt="Foto del reporte {index + 1}" 
                        class="w-full h-full object-cover"
                      />
                    </button>
                  {/each}
                </div>
              </div>
            {:else}
              <div class="text-center py-6 bg-gray-50 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">Sin fotografías</p>
              </div>
            {/if}
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            on:click={closeModal}
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
{/if}

<!-- Modal Crear/Editar Reporte -->
<CrearReporteModal
  isOpen={showCrearModal}
  onClose={handleModalClose}
  onSuccess={handleReporteCreado}
  reporte={reporteParaEditar}
/>



<!-- Modal Galería de Imágenes -->
<ImageGalleryModal
  isOpen={showImageGallery}
  images={galleryImages}
  initialIndex={galleryInitialIndex}
  onClose={closeImageGallery}
/>

<style>
  .line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
</style>
