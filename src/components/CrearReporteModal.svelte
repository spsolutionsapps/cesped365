<script>
  import { onMount } from 'svelte';
  import Modal from './Modal.svelte';
  import { reportesAPI, jardinesAPI } from '../services/api';

  export let isOpen = false;
  export let onClose = () => {};
  export let onSuccess = () => {};
  export let reporte = null; // Reporte para editar (opcional)

  let loading = false;
  let error = null;
  let jardines = [];
  
  // Estados de progreso
  let uploadProgress = 0; // 0-100
  let progressMessage = '';
  
  // Obtener fecha actual en timezone de Argentina
  function getFechaActual() {
    // Crear fecha en timezone de Buenos Aires
    const now = new Date();
    const offset = -3; // UTC-3 (Argentina)
    const localTime = now.getTime();
    const localOffset = now.getTimezoneOffset() * 60000;
    const argTime = new Date(localTime + localOffset + (offset * 3600000));
    
    const year = argTime.getFullYear();
    const month = String(argTime.getMonth() + 1).padStart(2, '0');
    const day = String(argTime.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  // Datos del formulario
  let formData = {
    garden_id: '',
    visit_date: getFechaActual(),
    grass_health: 'bueno',
    grass_color: 'bueno',
    grass_even: '1',
    spots: '0',
    weeds_visible: '0',
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

  // Imágenes: una sola lista. Cada ítem es { type: 'existing', id, image_url } o { type: 'new', file, dataUrl }
  let imageItems = [];
  let existingImageIds = []; // IDs que tenía el reporte al abrir (para saber cuáles eliminar al guardar)
  let lastLoadedReportId = null; // Evitar que el reactive recargue y pise los cambios del usuario

  onMount(async () => {
    await cargarJardines();
  });

  // Cargar datos del reporte UNA SOLA VEZ al abrir para editar (evitar recargas que pisan cambios)
  $: if (reporte && isOpen) {
    const reportId = reporte?.id;
    if (reportId && lastLoadedReportId !== reportId) {
      lastLoadedReportId = reportId;
      (async () => {
        await new Promise((r) => setTimeout(r, 100));
        try {
          const res = await reportesAPI.getById(reportId);
          if (res.success && res.data) {
            cargarDatosReporte(res.data);
          } else {
            cargarDatosReporte(reporte);
          }
        } catch (err) {
          console.error('Error cargando reporte para editar:', err);
          cargarDatosReporte(reporte);
        }
      })();
    }
  }

  // Al cerrar el modal, resetear para que la próxima apertura vuelva a cargar
  $: if (!isOpen) {
    if (lastLoadedReportId != null) lastLoadedReportId = null;
  }

  // Resetear formulario cuando se cierra sin reporte
  $: if (!isOpen && !reporte) {
    resetForm();
  }

  async function cargarJardines() {
    try {
      const response = await jardinesAPI.getAll();
      if (response.success) {
        jardines = response.data;
        if (jardines.length > 0 && !reporte) {
          formData.garden_id = jardines[0].id;
        }
      }
    } catch (err) {
      console.error('Error cargando jardines:', err);
    }
  }

  function cargarDatosReporte(reporteData = null) {
    const data = reporteData ?? reporte;
    if (!data) return;

    // Cargar datos del reporte en el formulario
    formData = {
      garden_id: data.garden_id || '',
      visit_date: reporte.fecha || getFechaActual(),
      grass_health: reporte.estadoGeneral || 'bueno',
      grass_color: reporte.grass_color || (reporte.colorOk ? 'bueno' : 'regular'),
      grass_even: (reporte.grass_even ?? (reporte.cespedParejo ? 1 : 0)).toString(),
      spots: (reporte.spots ?? (reporte.manchas ? 1 : 0)).toString(),
      weeds_visible: (reporte.weeds_visible ?? (reporte.malezasVisibles ? 1 : 0)).toString(),
      technician_notes: reporte.jardinero || '',
      growth_cm: reporte.crecimientoCm ? String(reporte.crecimientoCm) : '',
      pest_detected: reporte.plagas || false,
      pest_description: reporte.pest_description || '',
      work_done: reporte.work_done || '',
      recommendations: reporte.recommendations || reporte.observaciones || '',
      grass_height_cm: reporte.grass_height_cm ? String(reporte.grass_height_cm) : '',
      watering_status: reporte.watering_status || 'optimo',
      fertilizer_applied: reporte.fertilizer_applied || false,
      fertilizer_type: reporte.fertilizer_type || '',
      weather_conditions: reporte.weather_conditions || '',
      next_visit: reporte.next_visit || ''
    };
    
    // Cargar imágenes existentes (API devuelve { id, image_url }); normalizar id a número
    if (data.imagenes && data.imagenes.length > 0) {
      imageItems = data.imagenes.map((img) => ({
        type: 'existing',
        id: Number(typeof img === 'object' && img.id != null ? img.id : 0),
        image_url: typeof img === 'string' ? img : img.image_url
      })).filter((it) => it.id > 0);
      existingImageIds = data.imagenes
        .map((img) => (typeof img === 'object' && img.id != null ? Number(img.id) : null))
        .filter((id) => id != null && id > 0);
    } else {
      imageItems = [];
      existingImageIds = [];
    }
  }

  function handleImageSelect(e) {
    const files = Array.from(e.target.files);
    files.forEach((file) => {
      if (!file.type.startsWith('image/')) return;
      const newItem = { type: 'new', file, dataUrl: null };
      imageItems = [...imageItems, newItem];
      const reader = new FileReader();
      reader.onload = (ev) => {
        imageItems = imageItems.map((it) =>
          it.type === 'new' && it.file === file ? { ...it, dataUrl: ev.target.result } : it
        );
      };
      reader.readAsDataURL(file);
    });
  }

  function removeImage(index) {
    imageItems = imageItems.filter((_, i) => i !== index);
  }

  async function handleSubmit(e) {
    e.preventDefault();
    loading = true;
    error = null;
    uploadProgress = 5;
    progressMessage = 'Procesando...';

    const isEditing = !!reporte;
    let reporteId = reporte?.id;

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

      // 1. Crear o actualizar el reporte (20% del progreso)
      if (isEditing) {
        progressMessage = 'Actualizando reporte...';
        uploadProgress = 10;
        console.log('🔄 Actualizando reporte...');
        
        const reporteResponse = await reportesAPI.update(reporteId, dataToSend);
        console.log('✅ Respuesta del servidor:', reporteResponse);
        
        uploadProgress = 20;
        
        if (!reporteResponse.success) {
          throw new Error(reporteResponse.message || 'Error al actualizar el reporte');
        }
        
        console.log('✅ Reporte actualizado con ID:', reporteId);

        // Eliminar en el servidor las imágenes que el usuario quitó en el modal
        const currentExistingIds = imageItems.filter((i) => i.type === 'existing').map((i) => Number(i.id));
        const imagesToDelete = existingImageIds.filter((id) => !currentExistingIds.includes(Number(id)));
        const reporteIdNum = Number(reporteId);
        if (imagesToDelete.length > 0) {
          console.log('🗑️ Eliminando imágenes del servidor:', imagesToDelete, 'reporteId:', reporteIdNum);
        }
        for (const imageId of imagesToDelete) {
          try {
            await reportesAPI.deleteImage(reporteIdNum, Number(imageId));
            console.log('  ✓ Imagen', imageId, 'eliminada');
          } catch (err) {
            console.error('  ✗ Error eliminando imagen', imageId, err);
          }
        }
      } else {
        progressMessage = 'Creando reporte...';
        uploadProgress = 10;
        console.log('🔄 Creando reporte...');
        
        const reporteResponse = await reportesAPI.create(dataToSend);
        console.log('✅ Respuesta del servidor:', reporteResponse);
        
        uploadProgress = 20;
        
        if (!reporteResponse.success) {
          throw new Error(reporteResponse.message || 'Error al crear el reporte');
        }

        const nuevoReporteId = reporteResponse.data.id;
        console.log('✅ Reporte creado con ID:', nuevoReporteId);
        
        // Usar el nuevo ID para subir imágenes
        if (!reporteId) {
          reporteId = nuevoReporteId;
        }
      }

      // 2. Subir nuevas imágenes (80% del progreso)
      const newImageFiles = imageItems.filter((i) => i.type === 'new').map((i) => i.file);
      let uploadFailed = false;
      if (newImageFiles.length > 0) {
        console.log('📤 Subiendo', newImageFiles.length, 'imagen(es) nueva(s), reporteId:', reporteId);
        const progressPerImage = 80 / newImageFiles.length;
        let imagenesSubidas = 0;

        for (let i = 0; i < newImageFiles.length; i++) {
          const image = newImageFiles[i];
          progressMessage = `Subiendo imagen ${i + 1} de ${newImageFiles.length}...`;

          try {
            await reportesAPI.uploadImage(reporteId, image);
            imagenesSubidas++;
            uploadProgress = 20 + (imagenesSubidas * progressPerImage);
            console.log(`✅ Imagen ${imagenesSubidas}/${newImageFiles.length} subida`);
          } catch (imgErr) {
            const msg = imgErr.message || (imgErr.errors && Object.values(imgErr.errors).join(' ')) || 'Error subiendo imagen';
            console.error('Error subiendo imagen:', msg, imgErr);
            error = msg;
            uploadFailed = true;
            uploadProgress = 20 + (imagenesSubidas * progressPerImage);
          }
        }

        progressMessage = uploadFailed ? 'Error en subida' : 'Finalizando...';
        uploadProgress = 100;
        if (uploadFailed) {
          throw new Error(error || 'Error al subir una o más imágenes');
        }
        console.log(`✅ Total imágenes subidas: ${imagenesSubidas}/${newImageFiles.length}`);
      } else {
        uploadProgress = 100;
        progressMessage = 'Completado';
      }

      // Éxito
      console.log(`🎉 Reporte ${isEditing ? 'actualizado' : 'creado'} exitosamente`);
      await new Promise(resolve => setTimeout(resolve, 300));
      onSuccess();
      resetForm();
      onClose();
    } catch (err) {
      console.error(`❌ Error ${isEditing ? 'actualizando' : 'creando'} reporte:`, err);
      error = err.message || `Error al ${isEditing ? 'actualizar' : 'crear'} el reporte. Por favor, intenta de nuevo.`;
      uploadProgress = 0;
      progressMessage = '';
    } finally {
      loading = false;
    }
  }

  function resetForm() {
    formData = {
      garden_id: jardines[0]?.id || '',
      visit_date: getFechaActual(),
      grass_health: 'bueno',
      grass_color: 'bueno',
      grass_even: '1',
      spots: '0',
      weeds_visible: '0',
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
    imageItems = [];
    existingImageIds = [];
    error = null;
    uploadProgress = 0;
    progressMessage = '';
  }

  function handleClose() {
    if (!loading) {
      resetForm();
      onClose();
    }
  }
</script>

<Modal {isOpen} title={reporte ? "Editar Reporte" : "Crear Nuevo Reporte"} size="lg" onClose={handleClose}>
  <!-- Loading overlay centrado sobre el modal -->
  {#if loading}
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center">
        <div class="flex justify-center mb-4">
          <svg class="animate-spin h-12 w-12 text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <p class="text-gray-700 font-medium mb-3">{progressMessage || 'Procesando...'}</p>
        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
          <div 
            class="bg-primary-600 h-2 rounded-full transition-all duration-300 ease-out"
            style="width: {uploadProgress}%"
          ></div>
        </div>
        <p class="text-sm text-gray-500 mt-2">{Math.round(uploadProgress)}%</p>
      </div>
    </div>
  {/if}

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
              {jardin.user_name}
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

    <!-- Jardinero, Estado del Césped, Color y Estado de Riego -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label for="jardinero" class="block text-sm font-medium text-gray-700 mb-2">
          Nombre del Jardinero *
        </label>
        <input
          id="jardinero"
          type="text"
          bind:value={formData.technician_notes}
          required
          placeholder="Ej: Juan Pérez"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
      </div>

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
        </select>
      </div>

      <div>
        <label for="grass_color" class="block text-sm font-medium text-gray-700 mb-2">
          Color del Césped
        </label>
        <select
          id="grass_color"
          bind:value={formData.grass_color}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
          <option value="excelente">Excelente</option>
          <option value="bueno">Bueno</option>
          <option value="regular">Regular</option>
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
    <!-- Flags visuales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label for="grass_even" class="block text-sm font-medium text-gray-700 mb-2">
          Parejo
        </label>
        <select
          id="grass_even"
          bind:value={formData.grass_even}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>

      <div>
        <label for="spots" class="block text-sm font-medium text-gray-700 mb-2">
          Manchas
        </label>
        <select
          id="spots"
          bind:value={formData.spots}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>

      <div>
        <label for="weeds_visible" class="block text-sm font-medium text-gray-700 mb-2">
          Malezas
        </label>
        <select
          id="weeds_visible"
          bind:value={formData.weeds_visible}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>
    </div>

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

    <!-- Trabajo Realizado y Recomendaciones -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="work_done" class="block text-sm font-medium text-gray-700 mb-2">
          Trabajo Realizado
        </label>
        <textarea
          id="work_done"
          bind:value={formData.work_done}
          rows="3"
          placeholder="Corte, fertilización, control de plagas, etc."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
        ></textarea>
      </div>

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
    </div>

    <!-- Upload de Imágenes -->
    <div>
      <span class="block text-sm font-medium text-gray-700 mb-2">
        Imágenes
      </span>
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
      {#if imageItems.length > 0}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
          {#each imageItems as item, index}
            <div class="relative group">
              {#if item.type === 'existing' || item.dataUrl}
                <img
                  src={item.type === 'existing' ? item.image_url : item.dataUrl}
                  alt="Preview {index + 1}"
                  class="w-full h-32 object-cover rounded-lg"
                />
              {:else}
                <div class="w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-sm">Cargando...</div>
              {/if}
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
        {reporte ? 'Actualizando...' : 'Creando...'}
      {:else}
        {reporte ? 'Actualizar Reporte' : 'Crear Reporte'}
      {/if}
    </button>
  </div>
</Modal>
