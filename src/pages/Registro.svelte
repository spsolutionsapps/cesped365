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
    referidoPor: '' // Campo opcional
  };

  let planSeleccionado = 'Urbano';
  let loading = false;
  let error = null;
  let success = false;
  let emailYaExiste = false;

  // Obtener plan seleccionado de sessionStorage
  onMount(() => {
    const plan = sessionStorage.getItem('planSeleccionado');
    if (plan) {
      planSeleccionado = plan;
      formData.plan = plan;
      // Limpiar sessionStorage después de leerlo
      sessionStorage.removeItem('planSeleccionado');
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
      'Residencial': '$60.000',
      'Parque': '$90.000'
    };
    return precios[plan] || '';
  }

  async function handleSubmit(e) {
    e.preventDefault();
    loading = true;
    error = null;

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

<div class="min-h-screen bg-gradient-to-br from-primary-50 to-green-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-2xl w-full">
    <!-- Logo y título -->
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold text-primary-600 mb-2">Cesped365</h1>
      <h2 class="text-2xl font-semibold text-gray-900">Crear tu cuenta</h2>
      <p class="mt-2 text-gray-600">Completa el formulario para registrarte</p>
    </div>

    <!-- Plan seleccionado -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-6 border-2 border-green-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600 mb-1">Plan seleccionado:</p>
          <p class="text-xl font-bold text-green-600">{getPlanDisplayName(planSeleccionado)}</p>
          <p class="text-sm text-gray-500 mt-1">{getPlanPrice(planSeleccionado)}/mes</p>
        </div>
        <button
          type="button"
          on:click={cambiarPlan}
          class="text-sm text-primary-600 hover:text-primary-700 font-medium underline"
        >
          Cambiar plan
        </button>
      </div>
    </div>

    <!-- Formulario de registro -->
    <div class="bg-white rounded-2xl shadow-xl p-8">
      {#if success}
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
          <p class="font-semibold">¡Cuenta creada exitosamente!</p>
          <p class="text-sm mt-1">Serás redirigido al login en unos segundos...</p>
        </div>
      {:else}
        <form on:submit={handleSubmit} class="space-y-6">
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

          <!-- Nombre completo -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
              Nombre completo *
            </label>
            <input
              id="name"
              type="text"
              bind:value={formData.name}
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="Juan Pérez"
            />
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Correo electrónico *
            </label>
            <input
              id="email"
              type="email"
              bind:value={formData.email}
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="tu@email.com"
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
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="+54 11 1234-5678"
            />
          </div>

          <!-- Dirección del jardín -->
          <div>
            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
              Dirección del jardín
            </label>
            <textarea
              id="address"
              bind:value={formData.address}
              rows="2"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
              placeholder="Av. Corrientes 1234, CABA"
            ></textarea>
          </div>

          <!-- Referido por -->
          <div>
            <label for="referidoPor" class="block text-sm font-medium text-gray-700 mb-2">
              Referido por
            </label>
            <input
              id="referidoPor"
              type="text"
              bind:value={formData.referidoPor}
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="Nombre de quien te refirió (opcional)"
            />
            <p class="mt-1 text-xs text-gray-500">Campo opcional</p>
          </div>

          <!-- Contraseña -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              Contraseña *
            </label>
            <input
              id="password"
              type="password"
              bind:value={formData.password}
              required
              minlength="6"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="••••••••"
            />
            <p class="mt-1 text-xs text-gray-500">Mínimo 6 caracteres</p>
          </div>

          <button
            type="submit"
            disabled={loading}
            class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 font-semibold text-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
          >
            {loading ? 'Creando cuenta...' : 'Crear cuenta'}
          </button>
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
