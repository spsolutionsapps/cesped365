<script>
  import { onMount } from 'svelte';
  import { auth } from '../../stores/auth';
  import { suscripcionesAPI, authAPI } from '../../services/api';
  import Card from '../../components/Card.svelte';
  import Badge from '../../components/Badge.svelte';
  import EditarPerfilModal from '../../components/EditarPerfilModal.svelte';
  
  let currentUser;
  let userRole;
  let suscripcion = null;
  let loading = true;
  
  // Modales
  let showEditModal = false;
  let showPasswordModal = false;
  
  // Formulario de contraseña
  let passwordData = {
    current_password: '',
    new_password: '',
    confirm_password: ''
  };
  let passwordLoading = false;
  let passwordError = null;
  let passwordSuccess = null;
  let showCurrentPassword = false;
  let showNewPassword = false;
  let showConfirmPassword = false;

  // Tabs: 'personal' | 'seguridad' | 'suscripcion'
  let activeTab = 'personal';
  
  auth.subscribe(value => {
    currentUser = value.user;
    userRole = value.role;
  });
  
  // Cargar suscripción del backend
  onMount(async () => {
    if (userRole === 'cliente') {
      try {
        const response = await suscripcionesAPI.getMiSuscripcion();
        if (response.success && response.data) {
          suscripcion = {
            plan: response.data.planName,
            estado: { activa: 'Activo', cancelada: 'Cancelada', pausada: 'Pausada', vencida: 'Vencida' }[response.data.status] || 'Inactivo',
            fechaInicio: response.data.startDate,
            proximoPago: response.data.nextBillingDate,
            monto: `$${response.data.price.toLocaleString('es-AR')}`,
            frecuencia: response.data.frequency === 'mensual' ? 'Mensual' : response.data.frequency
          };
        }
        loading = false;
      } catch (err) {
        console.error('Error cargando suscripción:', err);
        loading = false;
      }
    } else {
      loading = false;
    }
  });
  
  function openEditModal() {
    showEditModal = true;
  }
  
  function closeEditModal() {
    showEditModal = false;
  }
  
  function handleProfileUpdated() {
    // Recargar datos del usuario
    authAPI.getCurrentUser().then(response => {
      if (response.success && response.user) {
        auth.updateUser({
          name: response.user.name,
          address: response.user.address
        });
      }
    });
  }
  
  async function handlePasswordSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const newPwd = (form.querySelector('#new-password').value || '').trim();
    const confirmPwd = (form.querySelector('#confirm-password').value || '').trim();
    
    if (newPwd !== confirmPwd) {
      passwordError = 'Las contraseñas nuevas no coinciden';
      return;
    }
    
    const dataToSend = {
      current_password: (form.querySelector('#current-password').value || '').trim(),
      new_password: newPwd,
      confirm_password: confirmPwd
    };
    
    passwordLoading = true;
    passwordError = null;
    passwordSuccess = null;

    try {
      const response = await authAPI.updatePassword(dataToSend);
      
      if (response.success) {
        passwordSuccess = 'Contraseña actualizada exitosamente';
        passwordData = { current_password: '', new_password: '', confirm_password: '' };
        form.querySelector('#current-password').value = '';
        form.querySelector('#new-password').value = '';
        form.querySelector('#confirm-password').value = '';
        // Limpiar mensaje después de 3 segundos
        setTimeout(() => {
          passwordSuccess = null;
        }, 3000);
      }
    } catch (err) {
      console.error('Error actualizando contraseña:', err);
      if (err.errors) {
        const errorMessages = Object.values(err.errors).flat();
        passwordError = errorMessages.join(', ') || err.message || 'Error al actualizar la contraseña';
      } else {
        passwordError = err.message || 'Error al actualizar la contraseña. Por favor, intenta de nuevo.';
      }
    } finally {
      passwordLoading = false;
    }
  }
</script>

<div class="py-6">
  <h2 class="mb-6 text-2xl font-semibold text-gray-700">
    Mi Perfil
  </h2>

  <!-- Tabs -->
  <div class="border-b border-gray-200 mb-6">
    <nav class="flex gap-1" aria-label="Tabs">
      <button
        type="button"
        on:click={() => activeTab = 'personal'}
        class="px-4 py-3 text-sm font-medium rounded-t-lg transition-colors {activeTab === 'personal'
          ? 'bg-white border border-b-0 border-gray-200 text-primary-600 -mb-px'
          : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'}"
      >
        Información Personal
      </button>
      <button
        type="button"
        on:click={() => activeTab = 'seguridad'}
        class="px-4 py-3 text-sm font-medium rounded-t-lg transition-colors {activeTab === 'seguridad'
          ? 'bg-white border border-b-0 border-gray-200 text-primary-600 -mb-px'
          : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'}"
      >
        Seguridad
      </button>
      {#if userRole === 'cliente'}
        <button
          type="button"
          on:click={() => activeTab = 'suscripcion'}
          class="px-4 py-3 text-sm font-medium rounded-t-lg transition-colors {activeTab === 'suscripcion'
            ? 'bg-white border border-b-0 border-gray-200 text-primary-600 -mb-px'
            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'}"
        >
          Suscripción
        </button>
      {/if}
    </nav>
  </div>

  <!-- Tab: Información Personal -->
  {#if activeTab === 'personal'}
    <Card title="Información Personal">
      <div class="space-y-3">
        <p class="text-gray-900">
          <span class="text-sm font-medium text-gray-600">Nombre completo:</span>
          <span class="ml-1 font-semibold">{currentUser?.name || 'N/A'}</span>
        </p>
        <p class="text-gray-900">
          <span class="text-sm font-medium text-gray-600">Correo electrónico:</span>
          <span class="ml-1 font-semibold">{currentUser?.email || 'N/A'}</span>
        </p>
        {#if userRole === 'cliente'}
          <p class="text-gray-900">
            <span class="text-sm font-medium text-gray-600">Teléfono:</span>
            <span class="ml-1 font-semibold">{currentUser?.phone || 'N/A'}</span>
          </p>
          <p class="text-gray-900">
            <span class="text-sm font-medium text-gray-600">Dirección:</span>
            <span class="ml-1 font-semibold">{currentUser?.address || 'N/A'}</span>
          </p>
        {/if}
        <p class="text-gray-900 flex items-center gap-2">
          <span class="text-sm font-medium text-gray-600">Rol:</span>
          {#if userRole === 'admin'}
            <Badge type="info">Administrador</Badge>
          {:else}
            <Badge type="success">Cliente</Badge>
          {/if}
        </p>
        <div class="pt-4">
          <button 
            on:click={openEditModal}
            class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 font-medium"
          >
            Editar información
          </button>
        </div>
      </div>
    </Card>
  {/if}

  <!-- Tab: Seguridad -->
  {#if activeTab === 'seguridad'}
    <Card title="Seguridad">
    <form on:submit={handlePasswordSubmit} class="space-y-4">
      {#if passwordError}
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
          {passwordError}
        </div>
      {/if}
      
      {#if passwordSuccess}
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
          {passwordSuccess}
        </div>
      {/if}
      
      <div>
        <label for="current-password" class="block text-sm font-medium text-gray-700 mb-2">
          Contraseña actual *
        </label>
        <div class="relative">
          <input
            id="current-password"
            type={showCurrentPassword ? 'text' : 'password'}
            value={passwordData.current_password}
            on:input={(e) => passwordData.current_password = e.target.value}
            required
            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="••••••••"
          />
          <button
            type="button"
            on:click={() => showCurrentPassword = !showCurrentPassword}
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
            aria-label={showCurrentPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'}
          >
            {#if showCurrentPassword}
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
            {:else}
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            {/if}
          </button>
        </div>
      </div>
      
      <div>
        <label for="new-password" class="block text-sm font-medium text-gray-700 mb-2">
          Nueva contraseña *
        </label>
        <div class="relative">
          <input
            id="new-password"
            type={showNewPassword ? 'text' : 'password'}
            value={passwordData.new_password}
            on:input={(e) => passwordData.new_password = e.target.value}
            required
            minlength="6"
            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="••••••••"
          />
          <button
            type="button"
            on:click={() => showNewPassword = !showNewPassword}
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
            aria-label={showNewPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'}
          >
            {#if showNewPassword}
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
            {:else}
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            {/if}
          </button>
        </div>
        <p class="mt-1 text-xs text-gray-500">Mínimo 6 caracteres</p>
      </div>
      
      <div>
        <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
          Confirmar nueva contraseña *
        </label>
        <div class="relative">
          <input
            id="confirm-password"
            type={showConfirmPassword ? 'text' : 'password'}
            value={passwordData.confirm_password}
            on:input={(e) => passwordData.confirm_password = e.target.value}
            required
            minlength="6"
            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="••••••••"
          />
          <button
            type="button"
            on:click={() => showConfirmPassword = !showConfirmPassword}
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
            aria-label={showConfirmPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'}
          >
            {#if showConfirmPassword}
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
            {:else}
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            {/if}
          </button>
        </div>
      </div>
      
      <button 
        type="submit"
        disabled={passwordLoading}
        class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {passwordLoading ? 'Cambiando...' : 'Cambiar contraseña'}
      </button>
    </form>
    </Card>
  {/if}

  <!-- Tab: Suscripción (solo clientes) -->
  {#if activeTab === 'suscripcion' && userRole === 'cliente' && !loading}
    <Card title="Suscripción">
      <div class="space-y-3">
        <p class="text-gray-900">
          <span class="text-sm font-medium text-gray-600">Plan actual:</span>
          <span class="ml-1 font-semibold">{suscripcion?.plan || 'N/A'}</span>
        </p>
        <p class="text-gray-900 flex items-center gap-2">
          <span class="text-sm font-medium text-gray-600">Estado:</span>
          <Badge type={suscripcion?.estado === 'Activo' ? 'success' : 'danger'}>
            {suscripcion?.estado || 'N/A'}
          </Badge>
        </p>
        <p class="text-gray-900">
          <span class="text-sm font-medium text-gray-600">Próximo pago:</span>
          <span class="ml-1 font-semibold">{suscripcion?.proximoPago || 'N/A'}</span>
        </p>
        <p class="text-gray-900">
          <span class="text-sm font-medium text-gray-600">Monto:</span>
          <span class="ml-1 font-semibold">{suscripcion?.monto || 'N/A'} / {suscripcion?.frecuencia || 'N/A'}</span>
        </p>
        <div class="pt-4 space-y-2">
          <button class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 font-medium">
            Cambiar plan
          </button>
          <button class="bg-white text-gray-700 px-6 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 font-medium">
            Historial de pagos
          </button>
        </div>
      </div>
    </Card>
  {/if}
</div>

<!-- Modal de editar perfil -->
<EditarPerfilModal
  isOpen={showEditModal}
  onClose={closeEditModal}
  onSuccess={handleProfileUpdated}
/>
