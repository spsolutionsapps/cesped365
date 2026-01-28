<script>
  import Modal from './Modal.svelte';
  import { scheduledVisitsAPI } from '../services/api';

  export let isOpen = false;
  export let visita = null; // Visita a reagendar
  export let onClose = () => {};
  export let onSuccess = () => {};

  let loading = false;
  let error = null;
  
  // Obtener fecha mínima (hoy)
  function getFechaMinima() {
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
    scheduled_date: '',
    scheduled_time: '',
    gardener_name: '',
    notes: ''
  };

  // Cuando se abre el modal, pre-llenar con datos de la visita
  $: if (isOpen && visita) {
    // Convertir fecha a formato YYYY-MM-DD
    const fecha = new Date(visita.scheduled_date);
    const year = fecha.getFullYear();
    const month = String(fecha.getMonth() + 1).padStart(2, '0');
    const day = String(fecha.getDate()).padStart(2, '0');
    
    formData = {
      scheduled_date: `${year}-${month}-${day}`,
      scheduled_time: visita.scheduled_time || '',
      gardener_name: visita.gardener_name || '',
      notes: visita.notes || ''
    };
  }

  async function handleSubmit() {
    if (!visita) return;
    
    error = null;
    
    if (!formData.scheduled_date) {
      error = 'La fecha es requerida';
      return;
    }

    try {
      loading = true;
      
      // 1. Cancelar la visita actual (cambiar status a 'cancelada')
      await scheduledVisitsAPI.update(visita.id, {
        status: 'cancelada',
        notes: (visita.notes || '') + (visita.notes ? ' | ' : '') + 'Postergada por lluvia'
      });

      // 2. Crear nueva visita con la nueva fecha
      const nuevaVisita = await scheduledVisitsAPI.create({
        garden_id: visita.garden_id,
        scheduled_date: formData.scheduled_date,
        scheduled_time: formData.scheduled_time || null,
        gardener_name: formData.gardener_name || null,
        notes: formData.notes || null
      });

      if (nuevaVisita.success) {
        onSuccess();
        onClose();
      } else {
        throw new Error(nuevaVisita.message || 'Error al reagendar la visita');
      }
    } catch (err) {
      console.error('Error reagendando visita:', err);
      error = err.message || 'Error al reagendar la visita. Intenta nuevamente.';
    } finally {
      loading = false;
    }
  }

  function handleClose() {
    error = null;
    formData = {
      scheduled_date: '',
      scheduled_time: '',
      gardener_name: '',
      notes: ''
    };
    onClose();
  }
</script>

<Modal isOpen={isOpen} onClose={handleClose} title="Reagendar Visita por Lluvia">
  <div class="space-y-4">
    {#if error}
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        {error}
      </div>
    {/if}

    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg text-sm">
      <p class="font-semibold mb-1">Visita original:</p>
      <p>
        {visita ? new Date(visita.scheduled_date).toLocaleDateString('es-AR', { 
          weekday: 'long', 
          day: 'numeric', 
          month: 'long',
          year: 'numeric'
        }) : ''}
        {visita?.scheduled_time ? ` a las ${visita.scheduled_time}` : ''}
      </p>
    </div>

    <div>
      <label for="nueva_fecha" class="block text-sm font-medium text-gray-700 mb-1">
        Nueva Fecha <span class="text-red-500">*</span>
      </label>
      <input
        id="nueva_fecha"
        type="date"
        bind:value={formData.scheduled_date}
        min={getFechaMinima()}
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        required
      />
    </div>

    <div>
      <label for="nueva_hora" class="block text-sm font-medium text-gray-700 mb-1">
        Nueva Hora
      </label>
      <input
        id="nueva_hora"
        type="time"
        bind:value={formData.scheduled_time}
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
      />
    </div>

    <div>
      <label for="jardinero" class="block text-sm font-medium text-gray-700 mb-1">
        Jardinero
      </label>
      <input
        id="jardinero"
        type="text"
        bind:value={formData.gardener_name}
        placeholder="Nombre del jardinero"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
      />
    </div>

    <div>
      <label for="notas" class="block text-sm font-medium text-gray-700 mb-1">
        Notas
      </label>
      <textarea
        id="notas"
        bind:value={formData.notes}
        rows="3"
        placeholder="Notas adicionales sobre la reagendación..."
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
      ></textarea>
    </div>

    <div class="flex gap-3 pt-4">
      <button
        on:click={handleClose}
        disabled={loading}
        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        Cancelar
      </button>
      <button
        on:click={handleSubmit}
        disabled={loading || !formData.scheduled_date}
        class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
      >
        {#if loading}
          <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Reagendando...
        {:else}
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Reagendar Visita
        {/if}
      </button>
    </div>
  </div>
</Modal>
