<script>
  import { onMount } from 'svelte';
  import Modal from './Modal.svelte';
  import { reportesAPI, jardinesAPI } from '../services/api';

  export let isOpen = false;
  export let onClose = () => {};
  export let onSuccess = () => {};

  let loading = false;
  let error = null;
  let jardines = [];
  
  // Obtener fecha actual SIN conversión UTC
  function getFechaActual() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  // Datos del formulario
  let formData = {
    garden_id: '',
    visit_date: getFechaActual(),
    grass_health: 'bueno',
    technician_notes: '',
    growth_cm: '',
    pest_detected: false,
    pest_description: '',
    work_done: '',
    recommendations: '',
    grass_height_cm: '',
    watering_status: 'optimo',
    fertilizer_applied: false,
    fertilizer_type: '',
    weather_conditions: '',
    next_visit: ''
  };

  // Imágenes
  let selectedImages = [];
  let imagePreviews = [];

  onMount(async () => {
    await cargarJardines();
  });

  async function cargarJardines() {
    try {
      const response = await jardinesAPI.getAll();
      if (response.success) {
        jardines = response.data;
        if (jardines.length > 0) {
          formData.garden_id = jardines[0].id;
        }
      }
    } catch (err) {
      console.error('Error cargando jardines:', err);
    }
  }

  function handleImageSelect(e) {
    const files = Array.from(e.target.files);
    
    files.forEach(file => {
      if (file.type.startsWith('image/')) {
        selectedImages = [...selectedImages, file];
        
        const reader = new FileReader();
        reader.onload = (e) => {
          imagePreviews = [...imagePreviews, e.target.result];
        };
        reader.readAsDataURL(file);
      }
    });
  }

  function removeImage(index) {
    selectedImages = selectedImages.filter((_, i) => i !== index);
    imagePreviews = imagePreviews.filter((_, i) => i !== index);
  }

  async function handleSubmit(e) {
    e.preventDefault();
    loading = true;
    error = null;

    try {
      // Preparar datos para enviar (convertir strings vacíos a null)
      const dataToSend = {
        ...formData,
        growth_cm: formData.growth_cm === '' ? null : formData.growth_cm,
        grass_height_cm: formData.grass_height_cm === '' ? null : formData.grass_height_cm,
        pest_description: formData.pest_description === '' ? null : formData.pest_description,
        fertilizer_type: formData.fertilizer_type === '' ? null : formData.fertilizer_type,
        next_visit: formData.next_visit === '' ? null : formData.next_visit
      };
      
      console.log('📅 Fecha que se enviará:', dataToSend.visit_date);
      console.log('📦 Datos a enviar:', dataToSend);

      // 1. Crear el reporte
      const reporteResponse = await reportesAPI.create(dataToSend);
      
      if (!reporteResponse.success) {
        throw new Error(reporteResponse.message || 'Error al crear el reporte');
      }

      const reporteId = reporteResponse.data.id;

      // 2. Subir imágenes
      if (selectedImages.length > 0) {
        for (const image of selectedImages) {
          await reportesAPI.uploadImage(reporteId, image);
        }
      }

      // Éxito
      onSuccess();
      resetForm();
      onClose();
    } catch (err) {
      console.error('Error creando reporte:', err);
      error = err.message || 'Error al crear el reporte. Por favor, intenta de nuevo.';
    } finally {
      loading = false;
    }
  }

  function resetForm() {
    formData = {
      garden_id: jardines[0]?.id || '',
      visit_date: getFechaActual(),
      grass_health: 'bueno',
      technician_notes: '',
      growth_cm: '',
      pest_detected: false,
      pest_description: '',
      work_done: '',
      recommendations: '',
      grass_height_cm: '',
      watering_status: 'optimo',
      fertilizer_applied: false,
      fertilizer_type: '',
      weather_conditions: '',
      next_visit: ''
    };
    selectedImages = [];
    imagePreviews = [];
    error = null;
  }

  function handleClose() {
    if (!loading) {
      resetForm();
      onClose();
    }
  }
</script>

<Modal {isOpen} title="Crear Nuevo Reporte" size="lg" onClose={handleClose}>
  <form on:submit={handleSubmit} class="space-y-6">
    {#if error}
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        {error}
      </div>
    {/if}

    <!-- Jardín y Fecha -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="garden" class="block text-sm font-medium text-gray-700 mb-2">
          Jardín *
        </label>
        <select
          id="garden"
          bind:value={formData.garden_id}
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
          {#each jardines as jardin}
            <option value={jardin.id}>
              {jardin.address} - {jardin.user_name}
            </option>
          {/each}
        </select>
      </div>

      <div>
        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
          Fecha *
        </label>
        <input
          id="date"
          type="date"
          bind:value={formData.visit_date}
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
      </div>
    </div>

    <!-- Estado del Césped y Técnico -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="grass_health" class="block text-sm font-medium text-gray-700 mb-2">
          Estado del Césped *
        </label>
        <select
          id="grass_health"
          bind:value={formData.grass_health}
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
          <option value="excelente">Excelente</option>
          <option value="bueno">Bueno</option>
          <option value="regular">Regular</option>
          <option value="malo">Malo</option>
        </select>
      </div>

      <div>
        <label for="watering_status" class="block text-sm font-medium text-gray-700 mb-2">
          Estado de Riego
        </label>
        <select
          id="watering_status"
          bind:value={formData.watering_status}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
          <option value="optimo">Óptimo</option>
          <option value="insuficiente">Insuficiente</option>
          <option value="excesivo">Excesivo</option>
        </select>
      </div>
    </div>

    <!-- Mediciones -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="grass_height_cm" class="block text-sm font-medium text-gray-700 mb-2">
          Altura del Césped (cm)
        </label>
        <input
          id="grass_height_cm"
          type="number"
          step="0.1"
          bind:value={formData.grass_height_cm}
          placeholder="ej: 5.5"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
      </div>

      <div>
        <label for="growth_cm" class="block text-sm font-medium text-gray-700 mb-2">
          Crecimiento desde última visita (cm)
        </label>
        <input
          id="growth_cm"
          type="number"
          step="0.1"
          bind:value={formData.growth_cm}
          placeholder="ej: 2.5"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
      </div>
    </div>

    <!-- Plagas y Fertilizante -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="checkbox" bind:checked={formData.pest_detected} class="rounded text-primary-600 focus:ring-primary-500" />
          <span class="text-sm font-medium text-gray-700">Plagas Detectadas</span>
        </label>
        {#if formData.pest_detected}
          <input
            type="text"
            bind:value={formData.pest_description}
            placeholder="Descripción de la plaga"
            class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          />
        {/if}
      </div>

      <div>
        <label class="flex items-center space-x-2 cursor-pointer">
          <input type="checkbox" bind:checked={formData.fertilizer_applied} class="rounded text-primary-600 focus:ring-primary-500" />
          <span class="text-sm font-medium text-gray-700">Fertilizante Aplicado</span>
        </label>
        {#if formData.fertilizer_applied}
          <input
            type="text"
            bind:value={formData.fertilizer_type}
            placeholder="Tipo de fertilizante"
            class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          />
        {/if}
      </div>
    </div>

    <!-- Trabajo Realizado -->
    <div>
      <label for="work_done" class="block text-sm font-medium text-gray-700 mb-2">
        Trabajo Realizado
      </label>
      <textarea
        id="work_done"
        bind:value={formData.work_done}
        rows="2"
        placeholder="Corte, fertilización, control de plagas, etc."
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
      ></textarea>
    </div>

    <!-- Recomendaciones y Notas del Técnico -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="recommendations" class="block text-sm font-medium text-gray-700 mb-2">
          Recomendaciones
        </label>
        <textarea
          id="recommendations"
          bind:value={formData.recommendations}
          rows="3"
          placeholder="Recomendaciones para el próximo mantenimiento..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
        ></textarea>
      </div>

      <div>
        <label for="technician_notes" class="block text-sm font-medium text-gray-700 mb-2">
          Notas del Técnico
        </label>
        <textarea
          id="technician_notes"
          bind:value={formData.technician_notes}
          rows="3"
          placeholder="Observaciones adicionales..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
        ></textarea>
      </div>
    </div>

    <!-- Upload de Imágenes -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Imágenes
      </label>
      <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-500 transition-colors">
        <input
          type="file"
          accept="image/*"
          multiple
          on:change={handleImageSelect}
          class="hidden"
          id="image-upload"
        />
        <label for="image-upload" class="cursor-pointer">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p class="mt-2 text-sm text-gray-600">
            Click para seleccionar imágenes o arrastra aquí
          </p>
          <p class="mt-1 text-xs text-gray-500">
            PNG, JPG hasta 2MB cada una
          </p>
        </label>
      </div>

      <!-- Preview de Imágenes -->
      {#if imagePreviews.length > 0}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
          {#each imagePreviews as preview, index}
            <div class="relative group">
              <img src={preview} alt="Preview {index + 1}" class="w-full h-32 object-cover rounded-lg" />
              <button
                type="button"
                on:click={() => removeImage(index)}
                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          {/each}
        </div>
      {/if}
    </div>
  </form>

  <div slot="footer" class="flex items-center gap-3">
    <button
      type="button"
      on:click={handleClose}
      disabled={loading}
      class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 transition-colors"
    >
      Cancelar
    </button>
    <button
      type="submit"
      on:click={handleSubmit}
      disabled={loading}
      class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 transition-colors flex items-center"
    >
      {#if loading}
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Creando...
      {:else}
        Crear Reporte
      {/if}
    </button>
  </div>
</Modal>
