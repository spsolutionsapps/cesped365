<script>
  import { onMount } from 'svelte';
  import { slide } from 'svelte/transition';
  import { reportesAPI, jardinesAPI } from '../../services/api';
  import { auth } from '../../stores/auth';
  import { reportesRefresh } from '../../stores/reportesRefresh';
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
  
  // Estado de reportes expandidos en mobile
  let expandedReportes = new Set();
  
  // Evaluación del servicio (cliente)
  let ratingElegido = 0;
  let feedbackTexto = '';
  let loadingEvaluacion = false;
  let errorEvaluacion = null;
  
  function toggleReporteExpand(reporteId) {
    if (expandedReportes.has(reporteId)) {
      expandedReportes.delete(reporteId);
    } else {
      expandedReportes.add(reporteId);
    }
    expandedReportes = expandedReportes; // Trigger reactivity
  }
  
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

  function siNo(value) {
    return value ? 'Sí' : 'No';
  }

  function capitalizar(value) {
    if (!value || typeof value !== 'string') return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
  }

  function getColorLabel(reporte) {
    // Preferimos el valor real guardado (grass_color). Fallback para reportes viejos.
    const val = reporte?.grass_color;
    if (val) return capitalizar(val);
    if (reporte?.colorOk === true) return 'Bueno';
    if (reporte?.colorOk === false) return 'Regular';
    return '-';
  }

  function getJardinInfoById(gardenId) {
    const jardin = jardines.find(j => String(j.id) === String(gardenId));
    if (!jardin) {
      return { cliente: '', direccion: '' };
    }
    return {
      cliente: jardin.user_name || '',
      direccion: jardin.address || ''
    };
  }

  function getReporteTitulo(reporte) {
    // Preferir datos que vienen directamente del reporte (backend ya los entrega)
    if (reporte?.cliente && reporte?.direccion) return `${reporte.cliente} — ${reporte.direccion}`;
    if (reporte?.direccion) return reporte.direccion;
    if (reporte?.cliente) return reporte.cliente;

    const { cliente, direccion } = getJardinInfoById(reporte.garden_id);
    if (cliente && direccion) return `${cliente} — ${direccion}`;
    if (direccion) return direccion;
    if (cliente) return cliente;
    return `Jardín #${reporte.garden_id}`;
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
        (getJardinInfoById(r.garden_id).direccion && getJardinInfoById(r.garden_id).direccion.toLowerCase().includes(termino)) ||
        (getJardinInfoById(r.garden_id).cliente && getJardinInfoById(r.garden_id).cliente.toLowerCase().includes(termino))
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
    if (estado === 'excelente' || estado === 'bueno') return 'success';
    if (estado === 'regular') return 'warning';
    return 'danger';
  }
  
  let loadingDetalle = false;
  async function selectReporte(reporte) {
    selectedReporte = reporte;
    loadingDetalle = true;
    try {
      const res = await reportesAPI.getById(reporte.id);
      if (res.success && res.data) {
        selectedReporte = { ...reporte, ...res.data };
      }
    } catch (err) {
      console.error('Error cargando detalle del reporte:', err);
    }
    loadingDetalle = false;
  }
  
  function closeModal() {
    selectedReporte = null;
    ratingElegido = 0;
    feedbackTexto = '';
    errorEvaluacion = null;
  }
  
  // Resetear formulario de evaluación al cambiar de reporte
  $: if (selectedReporte) {
    errorEvaluacion = null;
    if (selectedReporte.client_rating != null) {
      ratingElegido = 0;
      feedbackTexto = '';
    }
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
    reportesRefresh.trigger();
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
  
  async function enviarEvaluacion() {
    if (!selectedReporte || ratingElegido < 1 || ratingElegido > 5) return;
    loadingEvaluacion = true;
    errorEvaluacion = null;
    try {
      const res = await reportesAPI.submitRating(selectedReporte.id, {
        rating: ratingElegido,
        feedback: feedbackTexto.trim() || undefined
      });
      if (res.success && res.data) {
        selectedReporte = { ...selectedReporte, client_rating: res.data.client_rating, client_feedback: res.data.client_feedback };
        ratingElegido = 0;
        feedbackTexto = '';
        // Actualizar también en la lista
        reportes = reportes.map(r => r.id === selectedReporte.id ? { ...r, client_rating: selectedReporte.client_rating, client_feedback: selectedReporte.client_feedback } : r);
        aplicarFiltros();
      }
    } catch (err) {
      errorEvaluacion = err.message || 'Error al enviar la evaluación';
    }
    loadingEvaluacion = false;
  }
  
  // Contar filtros activos
  $: filtrosActivos = (busqueda ? 1 : 0) + 
                      (filtroJardin ? 1 : 0) + 
                      (filtroFechaDesde ? 1 : 0) + 
                      (filtroFechaHasta ? 1 : 0);
</script>

<div class="py-6 overflow-x-hidden min-w-0">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-gray-700">
      Reportes de Jardín
    </h2>
    
    <div class="flex items-center gap-4">
      <!-- Selector de vista (solo desktop) -->
      {#if !loading && reportesFiltrados.length > 0}
        <div class="hidden md:flex items-center gap-2 bg-gray-100 rounded-lg p-1">
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
          <div class="flex flex-col md:grid md:grid-cols-2 lg:grid-cols-4 gap-4">
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
      {#if userRole === 'cliente' && filtrosActivos === 0}
        <p class="text-gray-600">Aún no tienes reportes. Cuando el administrador envíe un reporte de tu jardín, aparecerá aquí y podrás evaluarlo.</p>
      {:else}
        <p class="text-gray-600">No se encontraron reportes con los filtros aplicados</p>
      {/if}
    </div>
  {:else}
    {#if vista === 'cards'}
      <!-- Vista de tarjetas (Desktop) -->
      <div class="hidden md:grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4 mt-6">
        {#each reportesPaginados as reporte}
      <Card className="overflow-hidden transition-all duration-200 hover:shadow-lg border border-[#e2e2e2] rounded-[8px]">
        <div class="space-y-4">
          <!-- Header del reporte: usuario, dirección y estado en renglones; luego fecha y jardinero -->
          <div class="min-w-0">
            <p class="text-base font-semibold text-gray-900 leading-snug capitalize">
              {reporte.cliente || getJardinInfoById(reporte.garden_id).cliente || '—'}
            </p>
            <p class="text-sm text-gray-900 mt-0.5">
              {reporte.direccion || getJardinInfoById(reporte.garden_id).direccion || '—'}
            </p>
            <p class="mt-1.5 text-sm">
              <span class="text-gray-600">Estado del césped:</span>
              <Badge type={getBadgeType(reporte.estadoGeneral)} className="ml-1.5 capitalize">
                {reporte.estadoGeneral}
              </Badge>
            </p>
            <div class="flex items-center justify-between gap-2 mt-2 text-sm text-gray-500">
              <span>
                {new Date(reporte.fecha).toLocaleDateString('es-AR', { 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                })}
              </span>
              <span class="shrink-0 text-right inline-block"><span class="text-gray-500">Por:</span> <span class="font-semibold text-gray-800">{reporte.jardinero || 'N/A'}</span></span>
            </div>
          </div>

          <!-- Indicadores con iconos -->
          <div class="bg-gray-50 rounded-lg p-3 grid grid-cols-2 gap-x-4 gap-y-2.5 text-xs">
            <div class="flex items-center gap-2">
              {#if reporte.cespedParejo || reporte.grass_even}
                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              {:else}
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              {/if}
              <span><span class="text-gray-500">Parejo</span> <span class="font-medium text-gray-800">{siNo(reporte.cespedParejo ?? reporte.grass_even)}</span></span>
            </div>
            <div class="flex items-center gap-2">
              {#if reporte.manchas || reporte.spots}
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
              {:else}
                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              {/if}
              <span><span class="text-gray-500">Manchas</span> <span class="font-medium text-gray-800">{siNo(reporte.manchas ?? reporte.spots)}</span></span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
              <span><span class="text-gray-500">Color</span> <span class="font-medium text-gray-800">{getColorLabel(reporte)}</span></span>
            </div>
            <div class="flex items-center gap-2">
              {#if reporte.malezasVisibles || reporte.weeds_visible}
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
              {:else}
                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              {/if}
              <span><span class="text-gray-500">Malezas</span> <span class="font-medium text-gray-800">{siNo(reporte.malezasVisibles ?? reporte.weeds_visible)}</span></span>
            </div>
          </div>

          <!-- Recomendaciones (vista previa: 2 renglones) -->
          {#if reporte.notaJardinero}
            <p class="text-sm text-gray-600 line-clamp-2 overflow-hidden text-ellipsis" title={reporte.notaJardinero}>
              <span class="font-medium text-gray-700">Recomendaciones:</span> {reporte.notaJardinero}
            </p>
          {/if}

          <!-- Crecimiento -->
          <div class="flex items-center text-sm text-gray-600 bg-white rounded-lg px-3 py-2 border border-gray-100">
            <svg class="w-5 h-5 text-primary-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <span>Crecimiento: <strong class="text-gray-900">{reporte.crecimientoCm} cm</strong></span>
          </div>

          <!-- Botones de acción -->
          <div class="flex gap-2 pt-2">
            <button
              on:click={() => selectReporte(reporte)}
              class="flex-1 flex items-center justify-center gap-2 bg-primary-600 text-white py-2.5 rounded-lg hover:bg-primary-700 font-medium text-sm transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
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
      <!-- Vista de tabla (Desktop) -->
      <div class="hidden md:block mt-6">
        <Card className="reportes-vista-tabla-card">
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
                    <div class="text-sm font-semibold text-gray-900 max-w-xs truncate">
                      {getReporteTitulo(reporte)}
                    </div>
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
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                      <div class="whitespace-nowrap"><span class="text-gray-600">Parejo:</span> <span class="font-semibold text-gray-900">{siNo(reporte.cespedParejo)}</span></div>
                      <div class="whitespace-nowrap"><span class="text-gray-600">Color:</span> <span class="font-semibold text-gray-900">{getColorLabel(reporte)}</span></div>
                      <div class="whitespace-nowrap"><span class="text-gray-600">Manchas:</span> <span class="font-semibold text-gray-900">{siNo(reporte.manchas)}</span></div>
                      <div class="whitespace-nowrap"><span class="text-gray-600">Malezas:</span> <span class="font-semibold text-gray-900">{siNo(reporte.malezasVisibles)}</span></div>
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
    
    <!-- Vista mobile (lista colapsable) -->
    <div class="block md:hidden mt-6 overflow-x-hidden min-w-0">
      <Card>
        <div class="space-y-3">
          {#each reportesPaginados as reporte}
            <div class="border border-gray-200 rounded-lg overflow-hidden min-w-0">
              <!-- Header del collapsable -->
              <button
                on:click={() => toggleReporteExpand(reporte.id)}
                class="w-full min-w-0 px-3 py-3 sm:px-4 bg-gray-50 hover:bg-gray-100 transition-colors flex items-center justify-between gap-2 text-left"
              >
                <div class="flex-1 min-w-0 overflow-hidden">
                  <p class="font-semibold text-gray-900 text-sm truncate capitalize">
                    {reporte.cliente || getJardinInfoById(reporte.garden_id).cliente || '—'}
                  </p>
                  <p class="text-xs text-gray-900 truncate mt-0.5">
                    {reporte.direccion || getJardinInfoById(reporte.garden_id).direccion || '—'}
                  </p>
                  <p class="mt-1 text-xs">
                    <span class="text-gray-600">Estado del césped:</span>
                    <Badge type={getBadgeType(reporte.estadoGeneral)} className="ml-1 capitalize">
                      {reporte.estadoGeneral}
                    </Badge>
                  </p>
                  <div class="flex items-center justify-between gap-2 text-xs mt-1.5">
                    <span class="text-gray-500">
                      {new Date(reporte.fecha).toLocaleDateString('es-AR', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                      })}
                    </span>
                    <span class="shrink-0 text-right inline-block"><span class="text-gray-500">Por:</span> <span class="font-semibold text-gray-800">{reporte.jardinero || 'N/A'}</span></span>
                  </div>
                </div>
                <svg 
                  class="w-5 h-5 text-gray-500 transition-transform duration-200 ml-2 flex-shrink-0 {expandedReportes.has(reporte.id) ? 'rotate-180' : ''}" 
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Contenido collapsable -->
              {#if expandedReportes.has(reporte.id)}
                <div class="px-4 py-4 bg-white border-t border-gray-200 space-y-3">
                  <!-- Evaluación técnica -->
                  <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Evaluación</p>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                      <div class="flex items-center gap-1">
                        <span class="text-gray-600">Parejo:</span>
                        <span class="font-semibold text-gray-900">{siNo(reporte.cespedParejo)}</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <span class="text-gray-600">Color:</span>
                        <span class="font-semibold text-gray-900">{getColorLabel(reporte)}</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <span class="text-gray-600">Manchas:</span>
                        <span class="font-semibold text-gray-900">{siNo(reporte.manchas)}</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <span class="text-gray-600">Malezas:</span>
                        <span class="font-semibold text-gray-900">{siNo(reporte.malezasVisibles)}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Crecimiento -->
                  <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Crecimiento</p>
                    <p class="text-sm text-gray-900 font-medium">{reporte.crecimientoCm} cm</p>
                  </div>

                  <!-- Recomendaciones (vista previa: 2 renglones) -->
                  {#if reporte.notaJardinero || reporte.observaciones}
                    <div>
                      <p class="text-sm text-gray-600 line-clamp-2 overflow-hidden text-ellipsis" title={reporte.notaJardinero || reporte.observaciones}>
                        <span class="font-medium text-gray-700">Recomendaciones:</span> {reporte.notaJardinero || reporte.observaciones}
                      </p>
                    </div>
                  {/if}

                  <!-- Acciones -->
                  <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Acciones</p>
                    <div class="flex space-x-2">
                      <button
                        on:click={() => selectReporte(reporte)}
                        class="flex-1 flex items-center justify-center px-3 py-2 text-xs font-medium text-primary-600 bg-primary-50 rounded-md hover:bg-primary-100"
                      >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Ver
                      </button>
                      {#if userRole === 'admin'}
                        <button
                          on:click={() => editarReporte(reporte)}
                          class="flex items-center px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>
                        <button
                          on:click={() => eliminarReporte(reporte.id)}
                          class="flex items-center px-3 py-2 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      {/if}
                    </div>
                  </div>
                </div>
              {/if}
            </div>
          {/each}
        </div>
      </Card>
    </div>

    <!-- Paginación (siempre visible) -->
    <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-4">
      <div class="text-sm text-gray-700">
        Página {paginaActual} de {totalPaginas || 1}
      </div>
      <div class="flex gap-2 flex-wrap justify-center">
        <button
          on:click={() => paginaActual = Math.max(1, paginaActual - 1)}
          disabled={paginaActual === 1 || totalPaginas <= 1}
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Anterior
        </button>
        
        <!-- Números de página (solo si hay más de 1 página) -->
        {#if totalPaginas > 1}
          <div class="hidden sm:flex gap-2">
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
          </div>
        {/if}

        <button
          on:click={() => paginaActual = Math.min(totalPaginas, paginaActual + 1)}
          disabled={totalPaginas <= 1 || paginaActual === totalPaginas}
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Siguiente
        </button>
      </div>
    </div>
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
              <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <div><span class="text-gray-600">Parejo:</span> <span class="font-semibold text-gray-900">{siNo(selectedReporte.cespedParejo)}</span></div>
                <div><span class="text-gray-600">Color:</span> <span class="font-semibold text-gray-900">{getColorLabel(selectedReporte)}</span></div>
                <div><span class="text-gray-600">Manchas:</span> <span class="font-semibold text-gray-900">{siNo(selectedReporte.manchas)}</span></div>
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
                <div><span class="text-gray-600">Malezas:</span> <span class="font-semibold text-gray-900">{siNo(selectedReporte.malezasVisibles)}</span></div>
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

            <!-- Trabajo Realizado y Recomendaciones (misma fila) -->
            {#if selectedReporte.work_done || selectedReporte.observaciones}
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {#if selectedReporte.work_done}
                  <div>
                    <h4 class="font-semibold text-gray-900 mb-2">🔧 Trabajo Realizado</h4>
                    <p class="text-gray-700 bg-gray-50 rounded-lg p-4 text-sm">
                      {selectedReporte.work_done}
                    </p>
                  </div>
                {/if}
                {#if selectedReporte.observaciones}
                  <div>
                    <h4 class="font-semibold text-gray-900 mb-2">💡 Recomendaciones</h4>
                    <p class="text-gray-700 bg-blue-50 rounded-lg p-4 text-sm">
                      {selectedReporte.observaciones}
                    </p>
                  </div>
                {/if}
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

            <!-- Evaluación del servicio: clientes siempre ven el formulario (o "Gracias" si ya evaluaron). La validación de "solo tu jardín" se hace al enviar. -->
            {#if userRole !== 'admin'}
              <div class="border-t border-gray-200 pt-6 mt-6">
                <h4 class="font-semibold text-gray-900 mb-3">Evaluación del servicio</h4>
                {#if selectedReporte.client_rating != null}
                  <!-- Ya evaluado: mostrar estrellas y comentario en solo lectura -->
                  <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-800 mb-2">Gracias por tu evaluación</p>
                    <div class="flex items-center gap-1 mb-2" aria-label="Valoración: {selectedReporte.client_rating} de 5">
                      {#each [1, 2, 3, 4, 5] as star}
                        <svg class="w-6 h-6 {star <= selectedReporte.client_rating ? 'text-amber-400' : 'text-gray-300'}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                      {/each}
                    </div>
                    {#if selectedReporte.client_feedback}
                      <p class="text-sm text-gray-700 mt-2 italic">"{selectedReporte.client_feedback}"</p>
                    {/if}
                  </div>
                {:else}
                  <!-- Formulario: estrellas + comentario. Si el reporte no es de su jardín, el backend devolverá 403 al enviar y se mostrará el mensaje. -->
                  <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                    <p class="text-sm text-gray-600">¿Cómo fue tu experiencia con este servicio?</p>
                    <div class="flex items-center gap-1" role="group" aria-label="Selecciona de 1 a 5 estrellas">
                      {#each [1, 2, 3, 4, 5] as star}
                        <button
                          type="button"
                          on:click={() => ratingElegido = star}
                          class="p-1 rounded focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 transition-transform hover:scale-110"
                          aria-pressed={ratingElegido === star}
                          aria-label="{star} estrella{star > 1 ? 's' : ''}"
                        >
                          <svg class="w-8 h-8 {ratingElegido >= star ? 'text-amber-400' : 'text-gray-300'} hover:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                          </svg>
                        </button>
                      {/each}
                    </div>
                    <div>
                      <label for="feedback-reporte" class="block text-sm font-medium text-gray-700 mb-1">Comentario (opcional)</label>
                      <textarea
                        id="feedback-reporte"
                        bind:value={feedbackTexto}
                        rows="3"
                        placeholder="Cuéntanos qué te pareció el servicio..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
                      ></textarea>
                    </div>
                    {#if errorEvaluacion}
                      <p class="text-sm text-red-600">{errorEvaluacion}</p>
                    {/if}
                    <button
                      type="button"
                      on:click={enviarEvaluacion}
                      disabled={ratingElegido < 1 || loadingEvaluacion}
                      class="w-full py-2 px-4 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-sm"
                    >
                      {loadingEvaluacion ? 'Enviando...' : 'Enviar evaluación'}
                    </button>
                  </div>
                {/if}
              </div>
            {:else if selectedReporte.client_rating != null}
              <!-- Admin: ver evaluación del cliente si existe -->
              <div class="border-t border-gray-200 pt-6 mt-6">
                <h4 class="font-semibold text-gray-900 mb-3">Evaluación del cliente</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="flex items-center gap-1 mb-2">
                    {#each [1, 2, 3, 4, 5] as star}
                      <svg class="w-5 h-5 {star <= selectedReporte.client_rating ? 'text-amber-400' : 'text-gray-300'}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                    {/each}
                    <span class="text-sm text-gray-600 ml-1">{selectedReporte.client_rating}/5</span>
                  </div>
                  {#if selectedReporte.client_feedback}
                    <p class="text-sm text-gray-700 italic">"{selectedReporte.client_feedback}"</p>
                  {/if}
                </div>
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

