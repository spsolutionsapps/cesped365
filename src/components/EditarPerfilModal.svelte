<script>
  import Modal from './Modal.svelte';
  import { authAPI } from '../services/api';
  import { auth } from '../stores/auth';

  export let isOpen = false;
  export let onClose = () => {};
  export let onSuccess = () => {};

  let loading = false;
  let error = null;
  
  let formData = {
    name: '',
    address: ''
  };
  
  let currentUser = null;
  let userRole = null;
  
  auth.subscribe(value => {
    currentUser = value.user;
    userRole = value.role;
  });

  // Cargar datos cuando se abre el modal
  $: if (isOpen && currentUser) {
    formData.name = currentUser.name || '';
    if (userRole === 'cliente') {
      formData.address = currentUser.address || '';
    }
  }

  async function handleSubmit(e) {
    e.preventDefault();
    loading = true;
    error = null;

    try {
      // Preparar datos según el rol
      const dataToSend = {
        name: formData.name
      };
      
      // Solo incluir address si es cliente
      if (userRole === 'cliente') {
        dataToSend.address = formData.address;
      }
      
      const response = await authAPI.updateProfile(dataToSend);
      
      if (response.success) {
        // Actualizar el store de auth
        const updatedData = {
          name: response.user.name
        };
        if (userRole === 'cliente') {
          updatedData.address = response.user.address;
        }
        auth.updateUser(updatedData);
        onSuccess();
        onClose();
      }
    } catch (err) {
      console.error('Error actualizando perfil:', err);
      if (err.errors) {
        const errorMessages = Object.values(err.errors).flat();
        error = errorMessages.join(', ') || err.message || 'Error al actualizar el perfil';
      } else {
        error = err.message || 'Error al actualizar el perfil. Por favor, intenta de nuevo.';
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

<Modal isOpen={isOpen} onClose={handleClose} title="Editar Información Personal">
  <form on:submit={handleSubmit} class="space-y-6">
    {#if error}
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        {error}
      </div>
    {/if}

    <div>
      <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
        Nombre completo *
      </label>
      <input
        id="name"
        type="text"
        bind:value={formData.name}
        required
        minlength="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        placeholder="Ej: Juan Pérez"
      />
    </div>

    {#if userRole === 'cliente'}
      <div>
        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
          Dirección
        </label>
        <input
          id="address"
          type="text"
          bind:value={formData.address}
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          placeholder="Ej: Av. Siempre Viva 123"
        />
      </div>
    {/if}

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
        {loading ? 'Guardando...' : 'Guardar Cambios'}
      </button>
    </div>
  </form>
</Modal>
