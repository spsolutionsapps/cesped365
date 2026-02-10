<script>
  import { onMount } from 'svelte';
  import { auth } from '../../stores/auth';
  import { reportesAPI, getApiBaseUrl } from '../../services/api';
  import Card from '../../components/Card.svelte';
  import Badge from '../../components/Badge.svelte';
  import ImageGalleryModal from '../../components/ImageGalleryModal.svelte';
  
  let userRole;
  let userName;
  let loading = true;
  let error = null;
  
  // Datos
  let reportes = [];
  let suscripcion = null;
  let loadingSuscripcion = true;
  
  // Galería de imágenes
  let showImageGallery = false;
  let galleryImages = [];
  let galleryInitialIndex = 0;
  
  auth.subscribe(value => {
    userRole = value.role;
    userName = value.user?.name;
  });
  
  // Cargar datos del backend
  onMount(async () => {
    await Promise.all([cargarReportes(), cargarSuscripcion()]);
  });
  
  async function cargarReportes() {
    try {
      loading = true;
      const response = await reportesAPI.getAll();
      if (response.success) {
        reportes = response.data || [];
      }
      loading = false;
    } catch (err) {
      console.error('Error cargando reportes:', err);
      error = 'Error al cargar los reportes. Verifica que el backend esté corriendo.';
      loading = false;
    }
  }
  
  async function cargarSuscripcion() {
    try {
      loadingSuscripcion = true;
      const API_BASE_URL = getApiBaseUrl();
      const response = await fetch(`${API_BASE_URL}/subscriptions/my-subscription`, {
        credentials: 'include'
      });
      const data = await response.json();
      if (data.success) {
        suscripcion = data.data;
      }
      loadingSuscripcion = false;
    } catch (err) {
      console.error('Error cargando suscripción:', err);
      loadingSuscripcion = false;
    }
  }
  
  function getBadgeType(estado) {
    if (estado === 'Bueno') return 'success';
    if (estado === 'Regular') return 'warning';
    return 'danger';
  }
  
  function estaAlDia() {
    if (!suscripcion || !suscripcion.nextBillingDate) return true;
    const hoy = new Date();
    const proximoVencimiento = new Date(suscripcion.nextBillingDate);
    const diasRestantes = Math.ceil((proximoVencimiento - hoy) / (1000 * 60 * 60 * 24));
    return diasRestantes > 3; // Más de 3 días restantes
  }
  
  function diasHastaVencimiento() {
    if (!suscripcion || !suscripcion.nextBillingDate) return null;
    const hoy = new Date();
    const proximoVencimiento = new Date(suscripcion.nextBillingDate);
    const diasRestantes = Math.ceil((proximoVencimiento - hoy) / (1000 * 60 * 60 * 24));
    return diasRestantes;
  }
  
  function formatearFecha(fecha) {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleDateString('es-AR', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
  }
  
  function openImageGallery(images, index = 0) {
    galleryImages = Array.isArray(images) ? images.map((img) => (typeof img === 'string' ? img : img.image_url)) : [];
    galleryInitialIndex = index;
    showImageGallery = true;
  }

  function closeImageGallery() {
    showImageGallery = false;
    galleryImages = [];
    galleryInitialIndex = 0;
  }
</script>

<div class="py-6">
  <!-- Page title -->
  <h2 class="mb-6 text-2xl font-semibold text-gray-700">
    Reporte de mi Jardín
  </h2>

  {#if error}
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
      {error}
    </div>
  {/if}

  {#if loading || loadingSuscripcion}
    <div class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      <p class="ml-4 text-gray-600">Cargando datos...</p>
    </div>
  {:else}
    <!-- Próximo vencimiento -->
    <div class="mb-8">
      <Card title="Próximo Vencimiento">
        {#if suscripcion}
          {#if estaAlDia()}
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
              <div class="flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-green-800 mb-2">Estás al día</h3>
              <p class="text-green-700 mb-4">
                Tu suscripción <strong>{suscripcion.planName}</strong> está activa
              </p>
              {#if suscripcion.nextBillingDate}
                <p class="text-sm text-green-600">
                  Próximo vencimiento: {formatearFecha(suscripcion.nextBillingDate)}
                </p>
              {/if}
            </div>
          {:else}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-xl font-semibold text-yellow-800 mb-2">¡Ponete al día!</h3>
                  <p class="text-yellow-700">
                    Tu suscripción <strong>{suscripcion.planName}</strong> se vence pronto
                  </p>
                  {#if suscripcion.nextBillingDate}
                    <p class="text-sm text-yellow-600 mt-2">
                      Vence el: {formatearFecha(suscripcion.nextBillingDate)}
                      {#if diasHastaVencimiento() !== null}
                        <span class="font-semibold">({diasHastaVencimiento()} días restantes)</span>
                      {/if}
                    </p>
                  {/if}
                </div>
                <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <button
                class="w-full mt-4 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-semibold"
              >
                Pagar Ahora
              </button>
            </div>
          {/if}
        {:else}
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
            <p class="text-gray-600">No tienes una suscripción activa</p>
            <button
              class="mt-4 px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
            >
              Ver Planes Disponibles
            </button>
          </div>
        {/if}
      </Card>
    </div>
    
    <!-- Reportes del jardín -->
    <Card title="Todos los Reportes de mi Jardín">
      {#if reportes.length > 0}
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {#each reportes as reporte}
            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-gray-900">
                  {new Date(reporte.fecha).toLocaleDateString('es-AR', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                  })}
                </h4>
                <Badge type={getBadgeType(reporte.estadoGeneral)}>
                  {reporte.estadoGeneral}
                </Badge>
              </div>
              
              <div class="space-y-2 text-sm text-gray-600 mb-4">
                <p><strong>Jardinero:</strong> {reporte.jardinero}</p>
                {#if reporte.tipo}
                  <p><strong>Tipo:</strong> {reporte.tipo}</p>
                {/if}
              </div>
              
              <div class="grid grid-cols-2 gap-2 text-xs mb-4">
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
                  <span>Césped parejo</span>
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
                  <span>Color saludable</span>
                </div>
              </div>
              
              {#if reporte.notaJardinero}
                <p class="text-xs text-gray-500 italic border-t border-gray-100 pt-3 mb-3">
                  "{reporte.notaJardinero}"
                </p>
              {/if}
              
              <!-- Imágenes del reporte -->
              {#if reporte.imagenes && reporte.imagenes.length > 0}
                <div class="border-t border-gray-100 pt-3">
                  <p class="text-xs font-medium text-gray-700 mb-2">
                    Fotografías ({reporte.imagenes.length})
                  </p>
                  <div class="grid grid-cols-2 gap-2">
                    {#each reporte.imagenes.slice(0, 4) as imagen, index}
                      <button
                        type="button"
                        on:click={() => openImageGallery(reporte.imagenes, index)}
                        class="w-full h-24 rounded-lg shadow-sm hover:shadow-md transition-shadow cursor-pointer overflow-hidden focus:outline-none focus:ring-2 focus:ring-primary-500"
                      >
                        <img
                          src={typeof imagen === 'string' ? imagen : imagen.image_url}
                          alt="Foto del reporte {index + 1}"
                          class="w-full h-full object-cover"
                          on:error={(e) => {
                            console.error('Error cargando imagen:', imagen);
                            e.target.style.display = 'none';
                          }}
                        />
                      </button>
                    {/each}
                  </div>
                  {#if reporte.imagenes.length > 4}
                    <button
                      type="button"
                      on:click={() => openImageGallery(reporte.imagenes, 0)}
                      class="mt-2 text-xs text-primary-600 hover:text-primary-700 font-medium"
                    >
                      Ver todas las {reporte.imagenes.length} fotografías →
                    </button>
                  {/if}
                </div>
              {/if}
            </div>
          {/each}
        </div>
      {:else}
        <div class="text-center py-12">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="text-gray-500 text-lg mb-2">No hay reportes aún</p>
          <p class="text-gray-400 text-sm">Los reportes aparecerán aquí después de cada visita del jardinero</p>
        </div>
      {/if}
    </Card>
  {/if}
</div>

<!-- Modal de galería de imágenes -->
<ImageGalleryModal
  isOpen={showImageGallery}
  images={galleryImages}
  initialIndex={galleryInitialIndex}
  onClose={closeImageGallery}
/>
