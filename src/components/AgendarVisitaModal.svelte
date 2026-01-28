<script>
  import { onMount } from 'svelte';
  import Modal from './Modal.svelte';
  import { scheduledVisitsAPI, jardinesAPI } from '../services/api';

  export let isOpen = false;
  export let onClose = () => {};
  export let onSuccess = () => {};

  let loading = false;
  let error = null;
  let jardines = [];
  
  // Obtener fecha y hora actual
  function getFechaActual() {
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

  function getHoraActual() {
    const now = new Date();
    const offset = -3;
    const localTime = now.getTime();
    const localOffset = now.getTimezoneOffset() * 60000;
    const argTime = new Date(localTime + localOffset + (offset * 3600000));
    
    const hours = String(argTime.getHours()).padStart(2, '0');
    const minutes = String(argTime.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
  }

  // Datos del formulario
  let formData = {
    garden_id: '',
    scheduled_date: getFechaActual(),
    scheduled_time: getHoraActual(),
    gardener_name: '',
    notes: ''
  };

  onMount(async () => {
    await cargarJardines();
  });

  async function cargarJardines() {
    try {
      const response = await jardinesAPI.getAll();
      if (response.success) {
        jardines = response.data || [];
        if (jardines.length > 0) {
          formData.garden_id = jardines[0].id;
        } else {
          error = 'No hay jardines disponibles. Por favor, crea un jardín primero.';
        }
      }
    } catch (err) {
      console.error('Error cargando jardines:', err);
      error = 'Error al cargar los jardines. Por favor, recarga la página.';
    }
  }
  
  // Recargar jardines cuando se abre el modal
  $: if (isOpen && jardines.length === 0 && !loading) {
    error = null;
    cargarJardines();
  }
  
  // Limpiar error al abrir el modal
  $: if (isOpen) {
    error = null;
  }

  async function handleSubmit(e) {
    e.preventDefault();
    loading = true;
    error = null;

    try {
      // Validar que garden_id esté seleccionado
      if (!formData.garden_id) {
        error = 'Por favor selecciona un jardín';
        loading = false;
        return;
      }

      const dataToSend = {
        garden_id: parseInt(formData.garden_id), // Asegurar que sea número
        scheduled_date: formData.scheduled_date,
        scheduled_time: formData.scheduled_time || null,
        gardener_name: formData.gardener_name || null,
        notes: formData.notes || null
      };

      console.log('Enviando datos de visita:', dataToSend);

      const response = await scheduledVisitsAPI.create(dataToSend);
      
      if (response.success) {
        onSuccess();
        // Resetear formulario
        formData = {
          garden_id: jardines.length > 0 ? jardines[0].id : '',
          scheduled_date: getFechaActual(),
          scheduled_time: getHoraActual(),
          gardener_name: '',
          notes: ''
        };
        onClose();
      } else {
        throw new Error(response.message || 'Error al agendar la visita');
      }
    } catch (err) {
      console.error('Error agendando visita:', err);
      // Mostrar errores de validación si existen
      if (err.errors) {
        const errorMessages = Object.values(err.errors).flat();
        error = errorMessages.join(', ') || err.message || 'Error al agendar la visita. Por favor, intenta de nuevo.';
      } else if (err.message) {
        error = err.message;
      } else {
        error = 'Error al agendar la visita. Por favor, verifica los datos e intenta de nuevo.';
      }
    } finally {
      loading = false;
    }
  }

  function handleClose() {
    if (!loading) {
      error = null;
      onClose();
    }
  }
</script>

<Modal isOpen={isOpen} onClose={handleClose} title="Agendar Visita">
  <form on:submit={handleSubmit} class="space-y-6">
    {#if error}
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        {error}
      </div>
    {/if}

    {#if jardines.length === 0 && !error}
      <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg text-sm">
        Cargando jardines...
      </div>
    {/if}

    <!-- Jardín y Fecha en la misma fila (desktop) -->
    <div class="flex flex-col sm:flex-row gap-4">
      <!-- Jardín -->
      <div class="flex-1">
        <label for="garden_id" class="block text-sm font-medium text-gray-700 mb-2">
          Jardín *
        </label>
        <select
          id="garden_id"
          bind:value={formData.garden_id}
          required
          disabled={jardines.length === 0}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
        >
          <option value="">Selecciona un jardín</option>
          {#each jardines as jardin}
            <option value={jardin.id}>
              {jardin.address || `Jardín #${jardin.id}`} {jardin.user_name ? `(${jardin.user_name})` : ''}
            </option>
          {/each}
        </select>
        {#if jardines.length === 0}
          <p class="mt-1 text-xs text-red-500">No hay jardines disponibles</p>
        {/if}
      </div>

      <!-- Fecha -->
      <div class="flex-1">
        <label for="scheduled_date" class="block text-sm font-medium text-gray-700 mb-2">
          Fecha *
        </label>
        <input
          id="scheduled_date"
          type="date"
          bind:value={formData.scheduled_date}
          required
          min={getFechaActual()}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
      </div>
    </div>

    <!-- Hora y Jardinero en la misma fila (desktop) -->
    <div class="flex flex-col sm:flex-row gap-4">
      <!-- Hora -->
      <div class="flex-1">
        <label for="scheduled_time" class="block text-sm font-medium text-gray-700 mb-2">
          Hora (opcional)
        </label>
        <input
          id="scheduled_time"
          type="time"
          bind:value={formData.scheduled_time}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
        <p class="mt-1 text-xs text-gray-500">Ejemplo: 09:00, 14:30</p>
      </div>

      <!-- Nombre del jardinero -->
      <div class="flex-1">
        <label for="gardener_name" class="block text-sm font-medium text-gray-700 mb-2">
          Jardinero (opcional)
        </label>
        <input
          id="gardener_name"
          type="text"
          bind:value={formData.gardener_name}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          placeholder="Nombre del jardinero asignado"
        />
      </div>
    </div>

    <!-- Notas -->
    <div>
      <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
        Notas (opcional)
      </label>
      <textarea
        id="notes"
        bind:value={formData.notes}
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
        placeholder="Notas adicionales sobre la visita..."
      ></textarea>
    </div>

    <!-- Botones -->
    <div class="flex gap-3 pt-4">
      <button
        type="button"
        on:click={handleClose}
        disabled={loading}
        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Cancelar
      </button>
      <button
        type="submit"
        disabled={loading}
        class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {loading ? 'Agendando...' : 'Agendar Visita'}
      </button>
    </div>
  </form>
</Modal>
