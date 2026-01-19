<script>
  import { onMount } from 'svelte';
  import { auth } from '../../stores/auth';
  import { dashboardAPI, reportesAPI, historialAPI } from '../../services/api';
  import StatCard from '../../components/StatCard.svelte';
  import Card from '../../components/Card.svelte';
  import Badge from '../../components/Badge.svelte';
  
  let userRole;
  let userName;
  let loading = true;
  let error = null;
  
  // Datos del dashboard
  let estadisticas = {};
  let ultimoReporte = null;
  let proximasVisitas = [];
  
  auth.subscribe(value => {
    userRole = value.role;
    userName = value.user?.name;
  });
  
  // Cargar datos del backend
  onMount(async () => {
    try {
      loading = true;
      
      // Cargar dashboard stats
      const dashboardResponse = await dashboardAPI.getDashboard();
      if (dashboardResponse.success) {
        estadisticas = dashboardResponse.data.estadisticas || {};
      }
      
      // Cargar reportes
      const reportesResponse = await reportesAPI.getAll();
      if (reportesResponse.success && reportesResponse.data.length > 0) {
        ultimoReporte = reportesResponse.data[0];
      }
      
      // Cargar historial
      const historialResponse = await historialAPI.getHistorial();
      if (historialResponse.success) {
        proximasVisitas = historialResponse.data.slice(0, 3);
      }
      
      loading = false;
    } catch (err) {
      console.error('Error cargando dashboard:', err);
      error = 'Error al cargar los datos. Verifica que el backend esté corriendo.';
      loading = false;
    }
  });
  
  // Iconos SVG
  const iconClientes = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>';
  const iconVisitas = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>';
  const iconReportes = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
  const iconEstado = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
  
  function getBadgeType(estado) {
    if (estado === 'Bueno') return 'success';
    if (estado === 'Regular') return 'warning';
    return 'danger';
  }
</script>

<div class="py-6">
  <!-- Page title -->
  <h2 class="mb-6 text-2xl font-semibold text-gray-700">
    Bienvenido, {userName}
  </h2>

  {#if error}
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
      {error}
      <p class="text-sm mt-2">Asegúrate de que el backend esté corriendo en http://localhost:8080</p>
    </div>
  {/if}

  {#if loading}
    <div class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      <p class="ml-4 text-gray-600">Cargando datos del dashboard...</p>
    </div>
  {:else}
    <!-- Stats cards -->
    {#if userRole === 'admin'}
      <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        <StatCard 
          title="Total Clientes" 
          value={(estadisticas.totalClientes || 0).toString()} 
          icon={iconClientes}
          color="primary"
        />
        <StatCard 
          title="Clientes Activos" 
          value={(estadisticas.clientesActivos || 0).toString()} 
          icon={iconEstado}
          color="green"
        />
        <StatCard 
          title="Visitas este mes" 
          value={(estadisticas.visitasEsteMes || 0).toString()} 
          icon={iconVisitas}
          color="blue"
        />
        <StatCard 
        title="Reportes totales" 
        value={(estadisticas.reportesTotales || 0).toString()} 
        icon={iconReportes}
        color="orange"
      />
      </div>
    {:else}
      {#if ultimoReporte}
        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">
          <StatCard 
            title="Estado del Jardín" 
            value={ultimoReporte.estadoGeneral} 
            icon={iconEstado}
            color={ultimoReporte.estadoGeneral === 'Bueno' ? 'green' : 'orange'}
          />
          <StatCard 
            title="Última Visita" 
            value={new Date(ultimoReporte.fecha).toLocaleDateString('es-AR')} 
            icon={iconVisitas}
            color="blue"
          />
          <StatCard 
            title="Total Reportes" 
            value={(estadisticas.reportesTotales || 0).toString()} 
            icon={iconReportes}
            color="primary"
          />
        </div>
      {/if}
    {/if}

    {#if ultimoReporte}
      <!-- Main content grid -->
      <div class="grid gap-6 mb-8 md:grid-cols-2">
        <!-- Último reporte -->
        <Card title="Último Reporte">
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-gray-600">Fecha:</span>
              <span class="text-sm text-gray-900">{new Date(ultimoReporte.fecha).toLocaleDateString('es-AR')}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm font-medium text-gray-600">Estado General:</span>
          <Badge type={getBadgeType(ultimoReporte.estadoGeneral)}>
            {ultimoReporte.estadoGeneral}
          </Badge>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm font-medium text-gray-600">Jardinero:</span>
          <span class="text-sm text-gray-900">{ultimoReporte.jardinero}</span>
        </div>
        
        <div class="pt-4 border-t border-gray-200">
          <h4 class="text-sm font-semibold text-gray-700 mb-3">Detalles:</h4>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="flex items-center">
              {#if ultimoReporte.cespedParejo}
                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              {:else}
                <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              {/if}
              <span class="text-gray-700">Césped parejo</span>
            </div>
            <div class="flex items-center">
              {#if ultimoReporte.colorOk}
                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              {:else}
                <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              {/if}
              <span class="text-gray-700">Color saludable</span>
            </div>
            <div class="flex items-center">
              {#if !ultimoReporte.manchas}
                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              {:else}
                <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              {/if}
              <span class="text-gray-700">Sin manchas</span>
            </div>
            <div class="flex items-center">
              {#if !ultimoReporte.malezasVisibles}
                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              {:else}
                <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              {/if}
              <span class="text-gray-700">Sin malezas</span>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-gray-200">
          <p class="text-sm text-gray-600 italic">"{ultimoReporte.notaJardinero}"</p>
        </div>

        <div class="pt-4">
          <a href="/dashboard/reportes" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
            Ver todos los reportes →
          </a>
        </div>
      </div>
    </Card>

    <!-- Próximas visitas / Historial reciente -->
    <Card title={userRole === 'admin' ? 'Próximas Visitas' : 'Historial Reciente'}>
      <div class="space-y-4">
        {#each proximasVisitas as visita}
          <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-900">{visita.tipo}</p>
                <p class="text-xs text-gray-500">{new Date(visita.fecha).toLocaleDateString('es-AR')}</p>
              </div>
            </div>
            <Badge type={getBadgeType(visita.estadoGeneral)}>
              {visita.estadoGeneral}
            </Badge>
          </div>
        {/each}
        
        <div class="pt-4">
          <a href="/dashboard/historial" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
            Ver historial completo →
          </a>
        </div>
      </Card>
      </div>
    {/if}
  {/if}
</div>
