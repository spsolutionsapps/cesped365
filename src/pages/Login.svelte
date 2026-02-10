<script>
  import { navigate } from 'svelte-routing';
  import { auth } from '../stores/auth';
  
  let email = '';
  let password = '';
  let error = '';
  let loading = false;
  let showPassword = false;

  function getRedirectPath() {
    if (typeof window === 'undefined') return null;
    const params = new URLSearchParams(window.location.search);
    const redirect = params.get('redirect');
    if (redirect && redirect.startsWith('/dashboard') && !redirect.includes('//')) return redirect;
    return null;
  }

  async function handleLogin(e) {
    e.preventDefault();
    error = '';
    loading = true;

    try {
      const result = await auth.login(email, password);
      
      if (result.success) {
        const redirectPath = getRedirectPath();
        if (redirectPath) {
          navigate(redirectPath, { replace: true });
        } else {
          const userRole = result.role;
          if (userRole === 'admin') {
            navigate('/dashboard/resumen', { replace: true });
          } else {
            navigate('/dashboard/reportes', { replace: true });
          }
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

<div class="min-h-screen bg-[url('/hero.jpg')] bg-cover bg-center bg-no-repeat flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative">
  <div class="absolute inset-0 bg-gradient-to-b from-primary-900/75 via-primary-800/65 to-primary-900/85"></div>
  <div class="max-w-md w-full relative z-10">
    <!-- Logo y título -->
    <div class="text-center mb-8">
      <img src="/logo.png" alt="Cesped365" class="w-[250px] mx-auto mb-4" />
      <h2 class="text-2xl font-semibold text-white">Acceso al Dashboard</h2>
      <p class="mt-2 text-white/90">Ingresa tus credenciales para continuar</p>
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
          <div class="relative">
            <input
              id="password"
              type={showPassword ? 'text' : 'password'}
              value={password}
              on:input={(e) => password = e.target.value}
              required
              class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="••••••••"
            />
            <button
              type="button"
              on:click={() => showPassword = !showPassword}
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
              aria-label={showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'}
            >
              {#if showPassword}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
              {:else}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              {/if}
            </button>
          </div>
        </div>

        <button
          type="submit"
          disabled={loading}
          class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 font-semibold text-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
        >
          {loading ? 'Ingresando...' : 'Ingresar'}
        </button>
      </form>

      <!-- Credenciales de prueba
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
      </div> -->
    </div>

    <!-- Link volver -->
    <div class="text-center mt-6">
      <a href="/" class="text-white/90 hover:text-white font-medium">
        ← Volver al inicio
      </a>
    </div>
  </div>
</div>
