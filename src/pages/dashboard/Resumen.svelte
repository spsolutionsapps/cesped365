<script>
  import { onMount, onDestroy } from 'svelte';
  import { Chart, CategoryScale, LinearScale, BarController, BarElement, Tooltip, Legend } from 'chart.js';
  import { auth } from '../../stores/auth';
  import { reportesRefresh } from '../../stores/reportesRefresh';
  import { dashboardAPI, reportesAPI, historialAPI, scheduledVisitsAPI } from '../../services/api';
  import StatCard from '../../components/StatCard.svelte';
  import Card from '../../components/Card.svelte';
  import Badge from '../../components/Badge.svelte';
  import Modal from '../../components/Modal.svelte';
  import ReagendarVisitaModal from '../../components/ReagendarVisitaModal.svelte';

  Chart.register(CategoryScale, LinearScale, BarController, BarElement, Tooltip, Legend);
  
  let userRole;
  let userName;
  let loading = true;
  let error = null;
  
  // Datos del dashboard
  let estadisticas = {};
  let ultimoReporte = null;
  let proximasVisitas = [];
  let visitasProgramadas = [];
  
  // Estado del modal de reagendar
  let visitaSeleccionada = null;
  let showReagendarModal = false;

  // Modal Agregar ganancia (solo admin)
  let showAgregarGananciaModal = false;
  let montoGanancia = '';
  let loadingGanancia = false;
  let errorGanancia = null;

  let unsubReportesRefresh;
  
  auth.subscribe(value => {
    userRole = value.role;
    userName = value.user?.name;
  });

  // Refrescar último reporte cuando se crea/edita uno desde Reportes (solo si no estamos ya cargando)
  unsubReportesRefresh = reportesRefresh.subscribe((n) => {
    if (n > 0 && !loading) cargarDatos();
  });
  
  // Función para cargar datos
  async function cargarDatos() {
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
      
      // Cargar visitas programadas (para clientes)
      if (userRole === 'cliente') {
        try {
          const visitsResponse = await scheduledVisitsAPI.getAll();
          if (visitsResponse.success) {
            visitasProgramadas = (visitsResponse.data || []).filter(v => v.status === 'programada').slice(0, 5);
          }
        } catch (err) {
          console.error('Error cargando visitas programadas:', err);
        }
      }
      
      loading = false;
    } catch (err) {
      console.error('Error cargando dashboard:', err);
      error = 'Error al cargar los datos. Verifica que el backend esté corriendo.';
      loading = false;
    }
  }
  
  // Cargar datos al entrar a la página (sin polling para evitar requests en loop)
  onMount(() => {
    cargarDatos();
  });

  // Iconos SVG
  const iconClientes = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>';
  const iconVisitas = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>';
  const iconReportes = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
  const iconEstado = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
  const iconGanancias = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
  
  function getBadgeType(estado) {
    if (estado === 'Bueno') return 'success';
    if (estado === 'Regular') return 'warning';
    return 'danger';
  }

  function siNo(reporte, campo) {
    if (!reporte) return '-';
    if (campo === 'parejo') {
      const v = reporte.grass_even ?? reporte.cespedParejo;
      if (v === undefined || v === null) return '-';
      return v === 1 || v === true ? 'Sí' : 'No';
    }
    if (campo === 'manchas') {
      const v = reporte.spots ?? reporte.manchas;
      if (v === undefined || v === null) return '-';
      return v === 1 || v === true ? 'Sí' : 'No';
    }
    if (campo === 'malezas') {
      const v = reporte.weeds_visible ?? reporte.malezasVisibles;
      if (v === undefined || v === null) return '-';
      return v === 1 || v === true ? 'Sí' : 'No';
    }
    return '-';
  }

  function getColorLabel(reporte) {
    if (!reporte) return '-';
    const val = reporte.grass_color;
    if (val) return val.charAt(0).toUpperCase() + val.slice(1);
    if (reporte.colorOk === true) return 'Bueno';
    if (reporte.colorOk === false) return 'Regular';
    return '-';
  }

  function abrirReagendarModal(visita) {
    visitaSeleccionada = visita;
    showReagendarModal = true;
  }

  async function handleReagendarSuccess() {
    showReagendarModal = false;
    visitaSeleccionada = null;
    // Recargar visitas programadas
    if (userRole === 'cliente') {
      try {
        const visitsResponse = await scheduledVisitsAPI.getAll();
        if (visitsResponse.success) {
          visitasProgramadas = (visitsResponse.data || []).filter(v => v.status === 'programada').slice(0, 5);
        }
      } catch (err) {
        console.error('Error recargando visitas programadas:', err);
      }
    }
  }

  function openAgregarGananciaModal() {
    montoGanancia = '';
    errorGanancia = null;
    showAgregarGananciaModal = true;
  }

  async function submitAgregarGanancia() {
    const num = parseFloat(String(montoGanancia).replace(',', '.'));
    if (isNaN(num) || num <= 0) {
      errorGanancia = 'Ingresá un monto mayor a cero.';
      return;
    }
    errorGanancia = null;
    loadingGanancia = true;
    try {
      await dashboardAPI.addGanancia(num);
      showAgregarGananciaModal = false;
      montoGanancia = '';
      await cargarDatos();
    } catch (err) {
      errorGanancia = err.message || 'Error al agregar la ganancia.';
    } finally {
      loadingGanancia = false;
    }
  }

  // Para el gráfico: altura relativa de las barras (mes actual vs anterior)
  // Gráficos Comparativa mensual (Chart.js)
  let chartMesRef;
  let chartDiaRef;
  let chartMesInstance = null;
  let chartDiaInstance = null;

  // Select "Mes por día": valor elegido (year-month) y datos para el gráfico
  let mesPorDiaValue = '';
  let gananciasPorDiaSeleccionado = [];
  let loadingMesPorDia = false;
  const nombresMes = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
  $: opcionesMesPorDia = (() => {
    const list = [];
    const d = new Date();
    for (let i = 0; i < 12; i++) {
      const y = d.getFullYear();
      const m = d.getMonth() + 1;
      list.push({ value: `${y}-${m}`, label: `${nombresMes[m - 1]} ${y}` });
      d.setMonth(d.getMonth() - 1);
    }
    return list;
  })();
  $: if (estadisticas?.gananciasMesPorDia?.length && !mesPorDiaValue) {
    const now = new Date();
    mesPorDiaValue = `${now.getFullYear()}-${now.getMonth() + 1}`;
    gananciasPorDiaSeleccionado = estadisticas.gananciasMesPorDia;
  }
  async function onMesPorDiaChange() {
    const [year, month] = mesPorDiaValue.split('-').map(Number);
    const now = new Date();
    const isCurrent = year === now.getFullYear() && month === now.getMonth() + 1;
    if (isCurrent && estadisticas?.gananciasMesPorDia?.length) {
      gananciasPorDiaSeleccionado = estadisticas.gananciasMesPorDia;
      return;
    }
    loadingMesPorDia = true;
    try {
      const res = await dashboardAPI.getGananciasPorDia(year, month);
      if (res.success && res.data?.gananciasMesPorDia) {
        gananciasPorDiaSeleccionado = res.data.gananciasMesPorDia;
      }
    } catch (e) {
      console.error('Error cargando ganancias por día:', e);
      gananciasPorDiaSeleccionado = [];
    } finally {
      loadingMesPorDia = false;
    }
  }

  $: if (typeof window !== 'undefined' && userRole === 'admin' && estadisticas?.gananciasPorMes?.length && chartMesRef) {
    if (chartMesInstance) chartMesInstance.destroy();
    const data = estadisticas.gananciasPorMes;
    chartMesInstance = new Chart(chartMesRef, {
      type: 'bar',
      data: {
        labels: data.map((d) => `${d.mes} ${String(d.año).slice(-2)}`),
        datasets: [{
          label: 'Ganancias ($)',
          data: data.map((d) => d.total),
          backgroundColor: 'rgba(37, 99, 235, 0.7)',
          borderColor: 'rgb(37, 99, 235)',
          borderWidth: 1,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => `$${Number(ctx.raw).toLocaleString('es-AR')}`,
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (v) => '$' + Number(v).toLocaleString('es-AR'),
            },
          },
        },
      },
    });
  }

  $: if (typeof window !== 'undefined' && userRole === 'admin' && gananciasPorDiaSeleccionado?.length && chartDiaRef) {
    if (chartDiaInstance) chartDiaInstance.destroy();
    const data = gananciasPorDiaSeleccionado;
    const now = new Date();
    const [selYear, selMonth] = (mesPorDiaValue || '').split('-').map(Number);
    const isCurrentMonth = selYear === now.getFullYear() && selMonth === now.getMonth() + 1;
    const hoy = now.getDate();
    chartDiaInstance = new Chart(chartDiaRef, {
      type: 'bar',
      data: {
        labels: data.map((d) => 'Día ' + d.dia),
        datasets: [{
          label: 'Ganancias ($)',
          data: data.map((d) => d.total),
          backgroundColor: data.map((d) => (isCurrentMonth && d.dia === hoy ? 'rgba(34, 197, 94, 0.8)' : 'rgba(148, 163, 184, 0.6)')),
          borderColor: data.map((d) => (isCurrentMonth && d.dia === hoy ? 'rgb(22, 163, 74)' : 'rgb(148, 163, 184)')),
          borderWidth: 1,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => `$${Number(ctx.raw).toLocaleString('es-AR')}`,
            },
          },
        },
        scales: {
          y: { beginAtZero: true },
          x: {
            ticks: {
              maxRotation: 45,
              maxTicksLimit: 15,
            },
          },
        },
      },
    });
  }

  onDestroy(() => {
    if (chartMesInstance) {
      chartMesInstance.destroy();
      chartMesInstance = null;
    }
    if (chartDiaInstance) {
      chartDiaInstance.destroy();
      chartDiaInstance = null;
    }
    if (typeof unsubReportesRefresh === 'function') unsubReportesRefresh();
  });
</script>

<div class="py-6">
  <!-- Page title -->
  <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <h2 class="text-2xl font-semibold text-gray-700">
      Bienvenido, {userName}
    </h2>
    {#if userRole === 'admin'}
      <button
        type="button"
        on:click={openAgregarGananciaModal}
        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors text-sm font-medium whitespace-nowrap"
      >
        Agregar ganancia
      </button>
    {/if}
  </div>

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
      <div class="grid gap-6 mb-8 grid-cols-1 md:grid-cols-3">
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
        <StatCard 
          title="Visitas del día" 
          value={(estadisticas.visitasHoy || 0).toString()} 
          icon={iconVisitas}
          color="primary"
        />
        <StatCard 
          title="Ganancias del mes" 
          value={'$' + (estadisticas.gananciasMes || 0).toLocaleString('es-AR')} 
          icon={iconGanancias}
          color="green"
        />
      </div>

      <!-- Comparativa mensual y Próximas visitas en la misma fila -->
      <div class="grid gap-6 mb-8 md:grid-cols-2">
        <Card title="Comparativa mensual">
          <div slot="headerAction" class="flex items-center gap-2">
            <label for="mes-por-dia" class="text-sm text-gray-600 whitespace-nowrap">Mes por día:</label>
            <select
              id="mes-por-dia"
              bind:value={mesPorDiaValue}
              on:change={onMesPorDiaChange}
              disabled={loadingMesPorDia}
              class="rounded-lg border border-gray-300 text-sm py-1.5 pl-2 pr-6 bg-white text-gray-700 focus:ring-primary-500 focus:border-primary-500"
            >
              {#each opcionesMesPorDia as opt}
                <option value={opt.value}>{opt.label}</option>
              {/each}
            </select>
            {#if loadingMesPorDia}
              <span class="text-xs text-gray-500">Cargando...</span>
            {/if}
          </div>
          <p class="text-sm text-gray-500 mb-2">Ganancias por mes (últimos 12 meses)</p>
          <div class="h-48 mb-6">
            <canvas bind:this={chartMesRef}></canvas>
          </div>
          <p class="text-sm text-gray-500 mb-2">Mes por día</p>
          <div class="h-40">
            <canvas bind:this={chartDiaRef}></canvas>
          </div>
        </Card>
        <Card title="Próximas Visitas">
          <div class="space-y-4">
            {#each proximasVisitas as visita}
                <div class="flex items-center py-3 border-b border-gray-100 last:border-0">
                  <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                      <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">{visita.cliente}</p>
                    <p class="text-xs text-gray-600">{visita.jardin}</p>
                    <p class="text-xs text-gray-500">{new Date(visita.fecha).toLocaleDateString('es-AR')}</p>
                  </div>
                </div>
            {/each}
            
            <div class="pt-4">
              <a href="/dashboard/agenda" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
                Ver toda la agenda →
              </a>
            </div>
          </div>
        </Card>
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

    <!-- Próximas visitas programadas para clientes (siempre visible) -->
    {#if userRole === 'cliente'}
      <div class="mb-8">
        <Card title="Próximas Visitas Programadas">
          <div class="space-y-4">
            {#if visitasProgramadas.length > 0}
              {#each visitasProgramadas as visita}
                <div class="py-3 border-b border-gray-100 last:border-0">
                  <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center flex-1">
                      <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                        </div>
                      </div>
                      <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-900">
                          {new Date(visita.scheduled_date).toLocaleDateString('es-AR', { 
                            weekday: 'long', 
                            day: 'numeric', 
                            month: 'long' 
                          })}
                        </p>
                        <p class="text-xs text-gray-500">
                          {#if visita.scheduled_time}
                            Hora: {visita.scheduled_time}
                          {/if}
                          {#if visita.gardener_name}
                            {visita.scheduled_time ? ' • ' : ''}Jardinero: {visita.gardener_name}
                          {/if}
                        </p>
                        {#if visita.notes}
                          <p class="text-xs text-gray-400 mt-1">{visita.notes}</p>
                        {/if}
                      </div>
                      <Badge type="info" class="ml-2">
                        Programada
                      </Badge>
                    </div>
                  </div>
                  <div class="flex justify-end mt-2">
                    <button
                      on:click={() => abrirReagendarModal(visita)}
                      class="px-3 py-1.5 text-xs bg-yellow-500 text-white rounded hover:bg-yellow-600 transition-colors flex items-center gap-1"
                      title="Postergar por lluvia"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                      </svg>
                      Postergar por lluvia
                    </button>
                  </div>
                </div>
              {/each}
            {:else}
              <p class="text-sm text-gray-500 text-center py-4">
                No hay visitas programadas por el momento
              </p>
            {/if}
          </div>
        </Card>
      </div>
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
          <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <div><span class="text-gray-600">Parejo:</span> <span class="font-semibold text-gray-900">{siNo(ultimoReporte, 'parejo')}</span></div>
            <div><span class="text-gray-600">Color:</span> <span class="font-semibold text-gray-900">{getColorLabel(ultimoReporte)}</span></div>
            <div><span class="text-gray-600">Manchas:</span> <span class="font-semibold text-gray-900">{siNo(ultimoReporte, 'manchas')}</span></div>
            <div><span class="text-gray-600">Malezas:</span> <span class="font-semibold text-gray-900">{siNo(ultimoReporte, 'malezas')}</span></div>
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
        
        <!-- Últimos usuarios registrados -->
        <Card title="Últimos 5 Usuarios Registrados">
          <div class="space-y-3">
            {#if estadisticas.ultimosUsuarios && estadisticas.ultimosUsuarios.length > 0}
              {#each estadisticas.ultimosUsuarios as usuario}
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                  <div>
                    <p class="text-sm font-medium text-gray-900">{usuario.name}</p>
                    <p class="text-xs text-gray-500">{usuario.email}</p>
                  </div>
                  <p class="text-xs text-gray-400">
                    {new Date(usuario.created_at).toLocaleDateString('es-AR')}
                  </p>
                </div>
              {/each}
            {:else}
              <p class="text-sm text-gray-500 text-center py-4">No hay usuarios registrados</p>
            {/if}
          </div>
        </Card>

      </div>
    {/if}
  {/if}
</div>

<!-- Modal Agregar ganancia -->
<Modal
  isOpen={showAgregarGananciaModal}
  title="Agregar ganancia"
  size="sm"
  onClose={() => { showAgregarGananciaModal = false; errorGanancia = null; }}
>
  <form on:submit|preventDefault={submitAgregarGanancia} class="space-y-4">
    {#if errorGanancia}
      <div class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded">{errorGanancia}</div>
    {/if}
    <div>
      <label for="monto-ganancia" class="block text-sm font-medium text-gray-700 mb-1">Monto</label>
      <input
        id="monto-ganancia"
        type="number"
        step="0.01"
        min="0.01"
        bind:value={montoGanancia}
        placeholder="Ej: 1500"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
      />
      <p class="mt-2 text-sm text-gray-500">Se sumará a la ganancia total del mes.</p>
    </div>
  </form>
  <div slot="footer" class="flex justify-end gap-3">
    <button
      type="button"
      on:click={() => { showAgregarGananciaModal = false; errorGanancia = null; }}
      class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
    >
      Cancelar
    </button>
    <button
      type="button"
      on:click={submitAgregarGanancia}
      disabled={loadingGanancia}
      class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50"
    >
      {loadingGanancia ? 'Guardando...' : 'Agregar'}
    </button>
  </div>
</Modal>

<!-- Modal de reagendar visita -->
<ReagendarVisitaModal
  isOpen={showReagendarModal}
  visita={visitaSeleccionada}
  onClose={() => {
    showReagendarModal = false;
    visitaSeleccionada = null;
  }}
  onSuccess={handleReagendarSuccess}
/>
