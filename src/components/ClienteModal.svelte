<script>
  import Modal from './Modal.svelte';
  import { clientesAPI } from '../services/api';

  export let isOpen = false;
  export let onClose = () => {};
  export let onSuccess = () => {};
  export let cliente = null; // Si es null, es crear. Si tiene datos, es editar

  let loading = false;
  let error = null;
  
  // Datos del formulario
  let formData = {
    name: '',
    email: '',
    phone: '',
    address: '',
    password: '',
    plan: 'Urbano',
    estado: 'Pendiente',
    lat: '',
    lng: ''
  };

  let lastClienteId = null;

  // Cuando se abre el modal, cargar datos si es edición
  $: if (isOpen) {
    if (cliente && cliente.id !== lastClienteId) {
      // Solo actualizar si es un cliente diferente
      lastClienteId = cliente.id;
      formData = {
        name: cliente.nombre || '',
        email: cliente.email || '',
        phone: cliente.telefono || '',
        address: cliente.direccion || '',
        password: '', // No mostrar password en edición
        plan: cliente.plan || 'Urbano',
        estado: cliente.estado || 'Pendiente',
        lat: (cliente.lat != null && cliente.lat !== '') ? String(cliente.lat) : '',
        lng: (cliente.lng != null && cliente.lng !== '') ? String(cliente.lng) : ''
      };
      console.log('📝 Modal abierto para editar. Plan del cliente:', cliente.plan);
      console.log('📝 formData.plan:', formData.plan);
    } else if (!cliente && lastClienteId !== null) {
      // Es crear nuevo
      lastClienteId = null;
      resetForm();
    }
  }

  async function handleSubmit(e) {
    e.preventDefault();
    loading = true;
    error = null;

    try {
      let response;
      
      if (cliente) {
        // Editar cliente existente
        const dataToSend = { ...formData };
        console.log('💾 Datos antes de enviar:', dataToSend);
        // No enviar password si está vacío
        if (!dataToSend.password) {
          delete dataToSend.password;
        }
        console.log('💾 Datos a enviar al backend:', dataToSend);
        response = await clientesAPI.update(cliente.id, dataToSend);
      } else {
        // Crear nuevo cliente
        if (!formData.password) {
          error = 'La contraseña es requerida para nuevos clientes';
          loading = false;
          return;
        }
        response = await clientesAPI.create(formData);
      }
      
      if (!response.success) {
        throw new Error(response.message || 'Error al guardar el cliente');
      }

      // Éxito
      onSuccess();
      resetForm();
      onClose();
    } catch (err) {
      console.error('Error guardando cliente:', err);
      if (err.errors && typeof err.errors === 'object') {
        const msgs = Object.values(err.errors);
        error = msgs.join('. ') || err.message;
      } else {
        error = err.message || 'Error al guardar el cliente. Por favor, intenta de nuevo.';
      }
    } finally {
      loading = false;
    }
  }

  function resetForm() {
    formData = {
      name: '',
      email: '',
      phone: '',
      address: '',
      password: '',
      plan: 'Urbano',
      estado: 'Pendiente',
      lat: '',
      lng: ''
    };
    error = null;
    lastClienteId = null;
  }

  function handleClose() {
    if (!loading) {
      resetForm();
      onClose();
    }
  }
</script>

<Modal {isOpen} title={cliente ? 'Editar Cliente' : 'Nuevo Cliente'} onClose={handleClose}>
  <form on:submit={handleSubmit} class="space-y-4">
    {#if error}
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        {error}
      </div>
    {/if}

    <!-- Nombre, Email y Teléfono en la misma línea en desktop -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Nombre -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
          Nombre Completo *
        </label>
        <input
          id="name"
          type="text"
          bind:value={formData.name}
          required
          placeholder="Juan Pérez"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
          Email *
        </label>
        <input
          id="email"
          type="email"
          bind:value={formData.email}
          required
          placeholder="juan@ejemplo.com"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
      </div>

      <!-- Teléfono -->
      <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
          Teléfono
        </label>
        <input
          id="phone"
          type="tel"
          bind:value={formData.phone}
          placeholder="+54 11 1234-5678"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
      </div>
    </div>

    <!-- Dirección -->
    <div>
      <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
        Dirección del Jardín
      </label>
      <textarea
        id="address"
        bind:value={formData.address}
        rows="2"
        placeholder="Av. Corrientes 1234, CABA"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
      ></textarea>
    </div>

    <!-- Coordenadas GPS (opcional) -->
    <div>
      <p class="block text-sm font-medium text-gray-700 mb-2">
        Coordenadas GPS (opcional)
      </p>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="lat" class="sr-only">Latitud</label>
          <input
            id="lat"
            type="text"
            inputmode="decimal"
            bind:value={formData.lat}
            placeholder="Ej: -34.6037"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          />
        </div>
        <div>
          <label for="lng" class="sr-only">Longitud</label>
          <input
            id="lng"
            type="text"
            inputmode="decimal"
            bind:value={formData.lng}
            placeholder="Ej: -58.3816"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          />
        </div>
      </div>
      <p class="mt-1 text-xs text-gray-500">Para ver la ubicación en Google Maps desde el detalle del cliente</p>
    </div>

    <!-- Plan -->
    <div>
      <label for="plan" class="block text-sm font-medium text-gray-700 mb-2">
        Plan *
      </label>
      <select
        id="plan"
        bind:value={formData.plan}
        on:change={(e) => console.log('🔄 Plan cambiado a:', e.target.value, 'formData.plan:', formData.plan)}
        required
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
      >
        <option value="Urbano">Urbano</option>
        <option value="Residencial">Residencial</option>
        <option value="Parque">Parque</option>
        <option value="Quintas">Quintas / Terrenos grandes</option>
      </select>
      <p class="mt-1 text-xs text-gray-500">
        Selecciona el tipo de jardín del cliente
      </p>
    </div>

    <!-- Estado (Activo / Pendiente / Cancelado) -->
    <div>
      <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
        Estado
      </label>
      <select
        id="estado"
        bind:value={formData.estado}
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
      >
        <option value="Pendiente">Pendiente</option>
        <option value="Activo">Activo</option>
        <option value="Cancelado">Cancelado</option>
      </select>
      <p class="mt-1 text-xs text-gray-500">
        Marcar como Activo si el cliente ya pagó por otro medio. Cancelado si dio de baja el servicio.
      </p>
    </div>

    <!-- Contraseña -->
    <div>
      <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
        Contraseña {cliente ? '(dejar vacío para no cambiar)' : '*'}
      </label>
      <input
        id="password"
        type="password"
        bind:value={formData.password}
        required={!cliente}
        placeholder="••••••••"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
      />
      {#if !cliente}
        <p class="mt-1 text-xs text-gray-500">Mínimo 6 caracteres</p>
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
        Guardando...
      {:else}
        {cliente ? 'Guardar Cambios' : 'Crear Cliente'}
      {/if}
    </button>
  </div>
</Modal>
