<script>
  import { onMount } from 'svelte';
  import { navigate } from 'svelte-routing';
  import { clientesAPI, getApiBaseUrl } from '../services/api';

  let formData = {
    name: '',
    email: '',
    phone: '',
    address: '',
    password: '',
    plan: 'Urbano', // Por defecto
    referidoPor: '', // Campo opcional
    lat: '',
    lng: ''
  };
  let gpsLoading = false;
  let gpsError = null;

  let planSeleccionado = 'Urbano';
  let loading = false;
  let error = null;
  let success = false;
  let emailYaExiste = false;
  let step = 1;
  let showPassword = false;
  let confirmEmail = '';
  let confirmPassword = '';

  // Obtener plan seleccionado de sessionStorage
  onMount(() => {
    const plan = sessionStorage.getItem('planSeleccionado');
    if (plan) {
      planSeleccionado = plan;
      formData.plan = plan;
      // No limpiamos sessionStorage aquí para que persista hasta el login y pago
    }
  });

  function cambiarPlan() {
    // Volver a la sección de planes
    navigate('/#categorias');
  }

  function getPlanDisplayName(plan) {
    const nombres = {
      'Urbano': 'Urbano',
      'Residencial': 'Residencial',
      'Parque': 'Parque / Quintas'
    };
    return nombres[plan] || plan;
  }

  function getPlanPrice(plan) {
    const precios = {
      'Urbano': '$45.000',
      'Residencial': '$90.000',
      'Parque': '$120.000'
    };
    return precios[plan] || '';
  }

  function nextStep() {
    error = null;
    if (!formData.name.trim() || !formData.email.trim() || !formData.password) {
      error = 'Completa nombre, correo y contraseña para continuar.';
      return;
    }
    if (formData.password.length < 6) {
      error = 'La contraseña debe tener al menos 6 caracteres.';
      return;
    }
    if (formData.email !== confirmEmail) {
      error = 'Los correos electrónicos no coinciden.';
      return;
    }
    if (formData.password !== confirmPassword) {
      error = 'Las contraseñas no coinciden.';
      return;
    }
    step = 2;
  }

  function prevStep() {
    error = null;
    step = 1;
  }

  function useMyLocation() {
    gpsError = null;
    if (!navigator.geolocation) {
      gpsError = 'Tu navegador no soporta geolocalización.';
      return;
    }
    gpsLoading = true;
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        formData.lat = pos.coords.latitude.toFixed(6);
        formData.lng = pos.coords.longitude.toFixed(6);
        gpsLoading = false;
        gpsError = null;
      },
      (err) => {
        gpsLoading = false;
        if (err.code === 1) {
          gpsError = 'Permiso denegado. Activa la ubicación para este sitio.';
        } else {
          gpsError = 'No se pudo obtener la ubicación. Intenta de nuevo.';
        }
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
  }

  async function handleSubmit(e) {
    e.preventDefault();
    error = null;
    if (formData.email !== confirmEmail) {
      error = 'Los correos electrónicos no coinciden.';
      return;
    }
    if (formData.password !== confirmPassword) {
      error = 'Las contraseñas no coinciden.';
      return;
    }
    loading = true;
    try {
      // Preparar datos para enviar
      const dataToSend = {
        name: formData.name,
        email: formData.email,
        password: formData.password,
        phone: formData.phone || null,
        address: formData.address || null,
        plan: formData.plan
      };

      // Agregar referidoPor solo si tiene valor
      if (formData.referidoPor && formData.referidoPor.trim() !== '') {
        dataToSend.referidoPor = formData.referidoPor.trim();
      }
      // Coordenadas GPS opcionales
      if (formData.lat !== '' && formData.lng !== '' && !isNaN(Number(formData.lat)) && !isNaN(Number(formData.lng))) {
        dataToSend.lat = Number(formData.lat);
        dataToSend.lng = Number(formData.lng);
      }

      // Usar endpoint público de registro con JSON
      const API_BASE_URL = getApiBaseUrl();
      const response = await fetch(`${API_BASE_URL}/registro`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify(dataToSend)
      });
      
      let result;
      try {
        result = await response.json();
      } catch (e) {
        // Si no se puede parsear JSON, leer como texto
        const text = await response.text();
        console.error('Respuesta no JSON:', text);
        throw new Error('Error de comunicación con el servidor');
      }
      
      console.log('Respuesta del servidor:', result);
      
      if (!response.ok) {
        // CodeIgniter devuelve errores en diferentes formatos
        let errorMessage = 'Error al crear la cuenta';
        
        // Si hay errores de validación (formato CodeIgniter con success: false)
        if (result.errors && typeof result.errors === 'object') {
          const errorMessages = Object.values(result.errors).flat();
          // Traducir mensajes técnicos a mensajes amigables
          const friendlyMessages = errorMessages.map(msg => {
            if (msg.includes('unique value') || msg.includes('is_unique')) {
              emailYaExiste = true;
              return 'Este correo electrónico ya está registrado.';
            }
            if (msg.includes('required')) {
              return 'Por favor, completa todos los campos requeridos.';
            }
            if (msg.includes('valid_email')) {
              return 'Por favor, ingresa un correo electrónico válido.';
            }
            if (msg.includes('min_length')) {
              return 'La contraseña debe tener al menos 6 caracteres.';
            }
            return msg;
          });
          errorMessage = friendlyMessages.join('. ');
        } 
        // Si hay un mensaje directo
        else if (result.message) {
          errorMessage = result.message;
        }
        // Si el resultado es un array de errores
        else if (Array.isArray(result) && result.length > 0) {
          errorMessage = result.join('. ');
        }
        // Si es un objeto con mensajes (formato fail() de CodeIgniter)
        else if (typeof result === 'object' && result !== null && !result.success) {
          const messages = Object.values(result).flat();
          if (messages.length > 0) {
            errorMessage = Array.isArray(messages[0]) ? messages[0].join('. ') : messages.join('. ');
          }
        }
        
        console.error('Error del servidor:', result);
        throw new Error(errorMessage);
      }
      
      if (!result.success) {
        throw new Error(result.message || 'Error al crear la cuenta');
      }

      // Éxito
      success = true;
      // Redirigir al login después de 2 segundos
      setTimeout(() => {
        navigate('/login');
      }, 2000);
    } catch (err) {
      console.error('Error registrando usuario:', err);
      error = err.message || 'Error al crear la cuenta. Por favor, intenta de nuevo.';
      // Resetear flag de email existente si no es ese error
      if (!error.includes('correo electrónico ya está registrado')) {
        emailYaExiste = false;
      }
    } finally {
      loading = false;
    }
  }
</script>

<div class="h-screen min-h-screen flex flex-col lg:flex-row overflow-hidden relative lg:bg-gray-100">
  <!-- Fondo mobile: imagen + overlay (como desktop, recortada con object-cover) -->
  <div class="lg:hidden absolute inset-0 z-0">
    <img src="/hero.jpg" alt="" class="absolute inset-0 w-full h-full object-cover object-center" aria-hidden="true" />
    <div class="absolute inset-0 bg-gradient-to-b from-primary-900/75 via-primary-800/65 to-primary-900/85"></div>
  </div>
  <!-- Panel izquierdo: imagen + plan (42% desktop) -->
  <div class="hidden lg:flex lg:w-[42%] h-screen relative flex-shrink-0">
    <img src="/hero.jpg" alt="Jardín" class="absolute inset-0 w-full h-full object-cover" />
    <div class="absolute inset-0 bg-gradient-to-b from-primary-900/75 via-primary-800/65 to-primary-900/85"></div>
    <!-- Logo y caja blanca con plan (centrados) -->
    <div class="absolute inset-0 flex flex-col items-center justify-center p-6">
      <img src="/logo.png" alt="Cesped365" class="w-[250px] mb-6" />
      <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 w-full max-w-sm">
        <p class="text-[18px] text-gray-600 mb-2">Plan seleccionado</p>
        <p class="text-[28px] font-bold text-primary-600">{getPlanDisplayName(planSeleccionado)}</p>
        <p class="text-[22px] font-semibold text-gray-800 mt-1">{getPlanPrice(planSeleccionado)}/mes</p>
        <button
          type="button"
          on:click={cambiarPlan}
          class="mt-4 text-[18px] text-primary-600 hover:text-primary-700 font-medium underline"
        >
          Cambiar plan
        </button>
      </div>
    </div>
  </div>

  <!-- Panel derecho: formulario (58% desktop, más ancho) -->
  <div class="flex-1 lg:w-[58%] h-screen flex items-center justify-center pt-[6.5rem] pb-8 lg:py-8 px-4 sm:px-6 lg:px-10 overflow-y-auto relative z-10">
    <div class="w-full max-w-lg lg:max-w-2xl">
      <!-- Logo/título en móvil (fila: logo izq, título der) -->
      <div class="lg:hidden flex items-center justify-between mb-6 mt-5">
        <img src="/logo.png" alt="Cesped365" class="w-[150px]" />
        <h2 class="text-xl font-semibold text-white">Crear tu cuenta</h2>
      </div>

      <!-- Plan en móvil -->
      <div class="lg:hidden bg-white rounded-2xl shadow-lg p-5 mb-6 border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Plan seleccionado</p>
            <p class="text-xl font-bold text-green-600">{getPlanDisplayName(planSeleccionado)}</p>
            <p class="text-sm text-gray-500">{getPlanPrice(planSeleccionado)}/mes</p>
          </div>
          <button type="button" on:click={cambiarPlan} class="text-sm text-primary-600 hover:text-primary-700 font-medium underline">
            Cambiar plan
          </button>
        </div>
      </div>

    <!-- Formulario de registro -->
    <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8">
      <h2 class="hidden lg:block text-2xl font-bold text-gray-900 mb-6">Crear tu cuenta</h2>
      {#if success}
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
          <p class="font-semibold">¡Cuenta creada exitosamente!</p>
          <p class="text-sm mt-1">Serás redirigido al login en unos segundos...</p>
        </div>
      {:else}
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-gray-700">
          <p class="font-semibold text-blue-800 mb-2">IMPORTANTE:</p>
          <p>Estos datos son necesarios para crear tu acceso al panel de seguimiento de tu jardín, donde vas a poder ver las visitas realizadas, fotos, observaciones y el estado del servicio.</p>
        </div>

        <!-- Indicador de pasos -->
        <div class="flex gap-2 mb-6">
          <div class="flex-1 h-1 rounded {step >= 1 ? 'bg-primary-600' : 'bg-gray-200'}"></div>
          <div class="flex-1 h-1 rounded {step >= 2 ? 'bg-primary-600' : 'bg-gray-200'}"></div>
        </div>

        <form on:submit={(e) => { e.preventDefault(); step === 1 ? nextStep() : handleSubmit(e); }} class="space-y-6">
          {#if error}
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
              <p>{error}</p>
              {#if emailYaExiste}
                <p class="mt-2 text-sm">
                  ¿Ya tienes una cuenta? 
                  <a href="/login" class="font-semibold underline hover:text-red-800">Inicia sesión aquí</a>
                </p>
              {/if}
            </div>
          {/if}

          {#if step === 1}
            <!-- Paso 1: nombre, correo, contraseña -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nombre completo *</label>
              <input
                id="name"
                type="text"
                bind:value={formData.name}
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder=""
              />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Correo electrónico *</label>
                <input
                  id="email"
                  type="email"
                  bind:value={formData.email}
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder=""
                />
              </div>
              <div>
                <label for="confirmEmail" class="block text-sm font-medium text-gray-700 mb-2">Confirmar correo *</label>
                <input
                  id="confirmEmail"
                  type="email"
                  bind:value={confirmEmail}
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder=""
                />
              </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Contraseña *</label>
                <div class="relative">
                  <input
                    id="password"
                    type={showPassword ? 'text' : 'password'}
                    value={formData.password}
                    on:input={(e) => formData.password = e.target.value}
                    minlength="6"
                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder=""
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
                <p class="mt-1 text-xs text-gray-500">Mínimo 6 caracteres</p>
              </div>
              <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">Confirmar contraseña *</label>
                <input
                  id="confirmPassword"
                  type="password"
                  bind:value={confirmPassword}
                  minlength="6"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder=""
                />
              </div>
            </div>
          {:else}
            <!-- Paso 2: teléfono, dirección, cómo nos conociste -->
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
              <input
                id="phone"
                type="tel"
                bind:value={formData.phone}
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder=""
              />
            </div>
            <div>
              <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Dirección del jardín</label>
              <textarea
                id="address"
                bind:value={formData.address}
                rows="2"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                placeholder=""
              ></textarea>
            </div>
            <div>
              <label for="referidoPor" class="block text-sm font-medium text-gray-700 mb-2">¿Cómo nos conociste?</label>
              <input
                id="referidoPor"
                type="text"
                bind:value={formData.referidoPor}
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder=""
              />
              <p class="mt-1 text-xs text-gray-500">Campo opcional</p>
            </div>
            <!-- Coordenadas GPS (opcional) -->
            <div>
              <p class="block text-sm font-medium text-gray-700 mb-2">Coordenadas GPS</p>
              <p class="text-xs text-gray-500 mb-2">Opcional. Sirve para que podamos ubicar tu jardín en el mapa.</p>
              <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[120px]">
                  <label for="lat" class="sr-only">Latitud</label>
                  <input
                    id="lat"
                    type="text"
                    inputmode="decimal"
                    bind:value={formData.lat}
                    placeholder="Ej: -34.6037"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  />
                </div>
                <div class="flex-1 min-w-[120px]">
                  <label for="lng" class="sr-only">Longitud</label>
                  <input
                    id="lng"
                    type="text"
                    inputmode="decimal"
                    bind:value={formData.lng}
                    placeholder="Ej: -58.3816"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  />
                </div>
                <button
                  type="button"
                  on:click={useMyLocation}
                  disabled={gpsLoading}
                  class="px-4 py-3 rounded-lg font-medium border border-primary-600 text-primary-600 hover:bg-primary-50 disabled:opacity-50 transition-colors"
                >
                  {#if gpsLoading}
                    Obteniendo...
                  {:else}
                    Usar mi ubicación
                  {/if}
                </button>
              </div>
              {#if gpsError}
                <p class="mt-2 text-sm text-red-600">{gpsError}</p>
              {/if}
            </div>
          {/if}

          <div class="flex gap-3">
            {#if step === 2}
              <button
                type="button"
                on:click={prevStep}
                class="flex-1 py-3 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition-all"
              >
                Volver
              </button>
            {/if}
            <button
              type="submit"
              disabled={loading}
              class="{step === 2 ? 'flex-1' : 'w-full'} bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 font-semibold text-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              {#if loading}
                Creando cuenta...
              {:else if step === 1}
                Siguiente
              {:else}
                Crear cuenta
              {/if}
            </button>
          </div>
        </form>
      {/if}

      <!-- Link volver -->
      <div class="text-center mt-6">
        <a href="/" class="text-primary-600 hover:text-primary-700 font-medium">
          ← Volver al inicio
        </a>
      </div>
    </div>
    </div>
  </div>
</div>
