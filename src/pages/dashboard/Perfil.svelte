<script>
  import { onMount } from 'svelte';
  import { auth } from '../../stores/auth';
  import { suscripcionesAPI } from '../../services/api';
  import Card from '../../components/Card.svelte';
  import Badge from '../../components/Badge.svelte';
  
  let currentUser;
  let userRole;
  let suscripcion = null;
  let loading = true;
  
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
          <button class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 font-medium">
            Editar información
          </button>
        </div>
      </div>
    </Card>

    <!-- Suscripción (solo clientes) -->
    {#if userRole === 'cliente'}
      <Card title="Mi Suscripción">
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-600">Plan actual</span>
            <span class="text-lg font-bold text-primary-600">{suscripcion.plan}</span>
          </div>
          
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-600">Estado</span>
            <Badge type="success">{suscripcion.estado}</Badge>
          </div>
          
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-600">Fecha de inicio</span>
            <span class="text-sm text-gray-900">
              {new Date(suscripcion.fechaInicio).toLocaleDateString('es-AR')}
            </span>
          </div>
          
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-600">Próximo pago</span>
            <span class="text-sm text-gray-900">
              {new Date(suscripcion.proximoPago).toLocaleDateString('es-AR')}
            </span>
          </div>
          
          <div class="pt-4 border-t border-gray-200">
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm font-medium text-gray-600">Monto</span>
              <span class="text-2xl font-bold text-gray-900">{suscripcion.monto}</span>
            </div>
            <p class="text-xs text-gray-500">{suscripcion.frecuencia}</p>
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
    {:else}
      <!-- Panel de admin -->
      <Card title="Configuración de Administrador">
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Como administrador, tienes acceso completo al sistema.
          </p>
          
          <div class="space-y-2">
            <button class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 font-medium">
              Gestionar usuarios
            </button>
            <button class="w-full bg-white text-gray-700 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 font-medium">
              Configuración del sistema
            </button>
            <button class="w-full bg-white text-gray-700 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 font-medium">
              Reportes del sistema
            </button>
          </div>
        </div>
      </Card>
    {/if}
  </div>

  <!-- Cambiar contraseña -->
  <Card title="Seguridad">
    <div class="space-y-4">
      <div>
        <label for="current-password" class="block text-sm font-medium text-gray-700 mb-2">
          Contraseña actual
        </label>
        <input
          id="current-password"
          type="password"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          placeholder="••••••••"
        />
      </div>
      
      <div>
        <label for="new-password" class="block text-sm font-medium text-gray-700 mb-2">
          Nueva contraseña
        </label>
        <input
          id="new-password"
          type="password"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          placeholder="••••••••"
        />
      </div>
      
      <div>
        <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
          Confirmar nueva contraseña
        </label>
        <input
          id="confirm-password"
          type="password"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          placeholder="••••••••"
        />
      </div>
      
      <button class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 font-medium">
        Cambiar contraseña
      </button>
    </div>
  </Card>
</div>
