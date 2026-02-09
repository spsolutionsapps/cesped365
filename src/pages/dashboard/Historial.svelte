<script>
  import { onMount } from 'svelte';
  import { historialAPI } from '../../services/api';
  import Card from '../../components/Card.svelte';
  import Badge from '../../components/Badge.svelte';
  
  let historial = [];
  let loading = true;
  let error = null;
  
  onMount(async () => {
    try {
      loading = true;
      const response = await historialAPI.getHistorial();
      if (response.success) {
        historial = response.data;
      }
      loading = false;
    } catch (err) {
      console.error('Error cargando historial:', err);
      error = 'Error al cargar el historial. Verifica que el backend esté corriendo.';
      loading = false;
    }
  });
  
  function getBadgeType(estado) {
    const e = (estado || '').toLowerCase();
    if (e === 'bueno' || e === 'excelente') return 'success';
    if (e === 'regular') return 'warning';
    return 'danger';
  }
</script>

<div class="py-6">
  <h2 class="mb-6 text-2xl font-semibold text-gray-700">
    Historial de Visitas
  </h2>

  <Card>
    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full whitespace-no-wrap">
        <thead>
          <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
            <th class="px-4 py-3">Jardín</th>
            <th class="px-4 py-3">Fecha</th>
            <th class="px-4 py-3">Valoración del cliente</th>
            <th class="px-4 py-3">Jardinero</th>
            <th class="px-4 py-3">Estado</th>
            <th class="px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y">
          {#each historial as visita}
            <tr class="text-gray-700">
              <td class="px-4 py-3 text-sm font-medium text-gray-900">
                {visita.jardin || '—'}
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center text-sm">
                  <div>
                    <p class="font-semibold">
                      {new Date(visita.fecha).toLocaleDateString('es-AR', { 
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                      })}
                    </p>
                    <p class="text-xs text-gray-600">
                      {new Date(visita.fecha).toLocaleDateString('es-AR', { 
                        weekday: 'long'
                      })}
                    </p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-sm">
                {#if visita.client_rating != null && visita.client_rating >= 1 && visita.client_rating <= 5}
                  <span class="inline-flex items-center gap-0.5" title="{visita.client_rating} de 5 estrellas">
                    {#each { length: 5 } as _, i}
                      <span class={i < visita.client_rating ? 'text-amber-500' : 'text-gray-300'} aria-hidden="true">★</span>
                    {/each}
                    <span class="ml-1 text-gray-600">({visita.client_rating}/5)</span>
                  </span>
                {:else}
                  <span class="text-gray-400">No evaluado</span>
                {/if}
              </td>
              <td class="px-4 py-3 text-sm">
                {visita.jardinero}
              </td>
              <td class="px-4 py-3 text-sm">
                <Badge type={getBadgeType(visita.estadoGeneral)}>
                  {visita.estadoGeneral}
                </Badge>
              </td>
              <td class="px-4 py-3 text-sm">
                <a 
                  href="/dashboard/reportes" 
                  class="text-primary-600 hover:text-primary-700 font-medium"
                >
                  Ver reporte
                </a>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>

    <!-- Pagination placeholder -->
    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Mostrando <span class="font-semibold">{historial.length}</span> visitas
        </div>
        <div class="flex space-x-2">
          <button class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50" disabled>
            Anterior
          </button>
          <button class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50" disabled>
            Siguiente
          </button>
        </div>
      </div>
    </div>
  </Card>

  <!-- Resumen estadístico -->
  <div class="grid gap-6 mt-8 md:grid-cols-3">
    <Card>
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-primary-100 text-primary-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Total de Visitas</p>
          <p class="text-2xl font-bold text-gray-900">{historial.length}</p>
        </div>
      </div>
    </Card>

    <Card>
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Estado Bueno</p>
          <p class="text-2xl font-bold text-gray-900">
            {historial.filter(v => ['bueno', 'excelente'].includes((v.estadoGeneral || '').toLowerCase())).length}
          </p>
        </div>
      </div>
    </Card>

    <Card>
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-orange-100 text-orange-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Requiere Atención</p>
          <p class="text-2xl font-bold text-gray-900">
            {historial.filter(v => (v.estadoGeneral || '').toLowerCase() === 'regular').length}
          </p>
        </div>
      </div>
    </Card>
  </div>
 
</div>
