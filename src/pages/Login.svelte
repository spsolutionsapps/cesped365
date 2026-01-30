<script>
  import { navigate } from 'svelte-routing';
  import { auth } from '../stores/auth';
  
  let email = '';
  let password = '';
  let error = '';
  let loading = false;

  async function handleLogin(e) {
    e.preventDefault();
    error = '';
    loading = true;

    try {
      // Llamar al backend real
      const result = await auth.login(email, password);
      
      if (result.success) {
        // Redirigir al dashboard según el rol
        // El rol viene en result.role después del login
        const userRole = result.role;
        if (userRole === 'admin') {
          navigate('/dashboard/resumen', { replace: true });
        } else {
          navigate('/dashboard/mi-jardin', { replace: true });
        }
      } else {
        error = result.error || 'Credenciales inválidas';
      }
    } catch (err) {
      error = 'Error al conectar con el servidor. Verifica que el backend esté corriendo.';
      console.error('Login error:', err);
    }
    
    loading = false;
  }
</script>

<div class="min-h-screen bg-gradient-to-br from-primary-50 to-green-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full">
    <!-- Logo y título -->
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold text-primary-600 mb-2">Cesped365</h1>
      <h2 class="text-2xl font-semibold text-gray-900">Acceso al Dashboard</h2>
      <p class="mt-2 text-gray-600">Ingresa tus credenciales para continuar</p>
    </div>

    <!-- Formulario de login -->
    <div class="bg-white rounded-2xl shadow-xl p-8">
      <form on:submit={handleLogin} class="space-y-6">
        {#if error}
          <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {error}
          </div>
        {/if}

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Correo electrónico
          </label>
          <input
            id="email"
            type="email"
            bind:value={email}
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="tu@email.com"
          />
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Contraseña
          </label>
          <input
            id="password"
            type="password"
            bind:value={password}
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="••••••••"
          />
        </div>

        <button
          type="submit"
          disabled={loading}
          class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 font-semibold text-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
        >
          {loading ? 'Ingresando...' : 'Ingresar'}
        </button>
      </form>

      <!-- Credenciales de prueba -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <p class="text-sm text-gray-600 font-medium mb-3">Credenciales de prueba:</p>
        <div class="space-y-2 text-sm">
          <div class="bg-gray-50 p-3 rounded-lg">
            <p class="font-semibold text-gray-700">Admin:</p>
            <p class="text-gray-600">Email: admin@cesped365.com</p>
            <p class="text-gray-600">Pass: admin123</p>
          </div>
          <div class="bg-gray-50 p-3 rounded-lg">
            <p class="font-semibold text-gray-700">Cliente:</p>
            <p class="text-gray-600">Email: cliente@example.com</p>
            <p class="text-gray-600">Pass: cliente123</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Link volver -->
    <div class="text-center mt-6">
      <a href="/" class="text-primary-600 hover:text-primary-700 font-medium">
        ← Volver al inicio
      </a>
    </div>
  </div>
</div>
