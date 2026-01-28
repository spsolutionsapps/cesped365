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
            estado: response.data.status === 'activa' ? 'Activo' : 'Inactivo',
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
    passwordLoading = true;
    passwordError = null;
    passwordSuccess = null;

    try {
      const response = await authAPI.updatePassword(passwordData);
      
      if (response.success) {
        passwordSuccess = 'Contraseña actualizada exitosamente';
        passwordData = {
          current_password: '',
          new_password: '',
          confirm_password: ''
        };
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

  <div class="grid gap-6 mb-8 md:grid-cols-2">
    <!-- Información personal -->
    <Card title="Información Personal">
      <div class="space-y-4">
        <div>
          <span class="text-sm font-medium text-gray-600">Nombre completo</span>
          <p class="mt-1 text-gray-900">{currentUser?.name || 'N/A'}</p>
        </div>

        <div>
          <span class="text-sm font-medium text-gray-600">Correo electrónico</span>
          <p class="mt-1 text-gray-900">{currentUser?.email || 'N/A'}</p>
        </div>
        
        {#if userRole === 'cliente'}
          <div>
            <span class="text-sm font-medium text-gray-600">Teléfono</span>
            <p class="mt-1 text-gray-900">{currentUser?.phone || 'N/A'}</p>
          </div>

          <div>
            <span class="text-sm font-medium text-gray-600">Dirección</span>
            <p class="mt-1 text-gray-900">{currentUser?.address || 'N/A'}</p>
          </div>
        {/if}
        
        <div>
          <span class="text-sm font-medium text-gray-600">Rol</span>
          <div class="mt-1">
            {#if userRole === 'admin'}
              <Badge type="info">Administrador</Badge>
            {:else}
              <Badge type="success">Cliente</Badge>
            {/if}
          </div>
        </div>
        
        <div class="pt-4">
          <button 
            on:click={openEditModal}
            class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 font-medium"
          >
            Editar información
          </button>
        </div>
      </div>
    </Card>

    <!-- Cambiar contraseña (Seguridad) -->
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
        <input
          id="current-password"
          type="password"
          bind:value={passwordData.current_password}
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          placeholder="••••••••"
        />
      </div>
      
      <div>
        <label for="new-password" class="block text-sm font-medium text-gray-700 mb-2">
          Nueva contraseña *
        </label>
        <input
          id="new-password"
          type="password"
          bind:value={passwordData.new_password}
          required
          minlength="6"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          placeholder="••••••••"
        />
        <p class="mt-1 text-xs text-gray-500">Mínimo 6 caracteres</p>
      </div>
      
      <div>
        <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
          Confirmar nueva contraseña *
        </label>
        <input
          id="confirm-password"
          type="password"
          bind:value={passwordData.confirm_password}
          required
          minlength="6"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          placeholder="••••••••"
        />
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
    
    {#if userRole === 'cliente' && !loading}
      <Card title="Suscripción">
        <div class="space-y-4">
          <div>
            <span class="text-sm font-medium text-gray-600">Plan actual</span>
            <p class="mt-1 text-gray-900">{suscripcion?.plan || 'N/A'}</p>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-600">Estado</span>
            <div class="mt-1">
              <Badge type={suscripcion?.estado === 'Activo' ? 'success' : 'danger'}>
                {suscripcion?.estado || 'N/A'}
              </Badge>
            </div>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-600">Próximo pago</span>
            <p class="mt-1 text-gray-900">{suscripcion?.proximoPago || 'N/A'}</p>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-600">Monto</span>
            <p class="mt-1 text-gray-900">{suscripcion?.monto || 'N/A'} / {suscripcion?.frecuencia || 'N/A'}</p>
          </div>
          
          <div class="pt-4 space-y-2">
            <button class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 font-medium">
              Cambiar plan
            </button>
            <button class="w-full bg-white text-gray-700 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 font-medium">
              Historial de pagos
            </button>
          </div>
        </div>
      </Card>
    {/if}
  </div>
</div>

<!-- Modal de editar perfil -->
<EditarPerfilModal
  isOpen={showEditModal}
  onClose={closeEditModal}
  onSuccess={handleProfileUpdated}
/>
