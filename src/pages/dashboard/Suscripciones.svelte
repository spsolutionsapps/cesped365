<script>
  import { onMount } from 'svelte';
  import { suscripcionesAPI, paymentAPI, authAPI } from '../../services/api';
  import Card from '../../components/Card.svelte';
  import Badge from '../../components/Badge.svelte';
  import Modal from '../../components/Modal.svelte';
  import { toasts } from '../../stores/toasts';

  let plans = [];
  let mySubscription = null;
  let currentUser = null;
  let selectedPlan = null; // Plan seleccionado durante el registro
  let loading = true;
  let error = null;
  let processingPayment = false;
  let showCancelModal = false;

  onMount(async () => {
    try {
      loading = true;
      
      // Cargar planes primero
      try {
        const plansRes = await suscripcionesAPI.getPlanes();
        if (plansRes.success && plansRes.data) {
          plans = plansRes.data;
        } else {
          error = "No se pudieron cargar los planes.";
        }
      } catch (e) {
        console.error("Error cargando planes", e);
        error = "No se pudieron cargar los planes: " + (e.message || 'Error desconocido');
      }
      
      // Cargar usuario actual
      try {
        const userRes = await authAPI.getCurrentUser();
        if (userRes.success && userRes.user) {
          currentUser = userRes.user;
          
          // Buscar el plan seleccionado durante el registro
          if (currentUser.plan && plans.length > 0) {
            // Intentar diferentes formas de coincidencia
            selectedPlan = plans.find(p => {
              const planName = p.name.toLowerCase().trim();
              const userPlan = currentUser.plan.toLowerCase().trim();
              
              // Coincidencia exacta
              if (planName === userPlan) return true;
              
              // Si el nombre del plan contiene el plan del usuario
              if (planName.includes(userPlan)) return true;
              
              // Si el plan del usuario contiene el nombre del plan
              if (userPlan.includes(planName)) return true;
              
              return false;
            });
            
            // Si no se encontró, intentar con el primer plan como fallback
            if (!selectedPlan && plans.length > 0) {
              console.log("No se encontró plan exacto, usando el primer plan disponible");
            }
          }
        }
      } catch (e) {
        console.error("Error cargando usuario", e);
        // Continuar aunque falle la carga del usuario
      }

      // Cargar mi suscripción (puede fallar si no tiene)
      try {
        const subRes = await suscripcionesAPI.getMiSuscripcion();
        if (subRes.success && subRes.data) {
          mySubscription = subRes.data;
        } else {
          console.log("Usuario sin suscripción activa (data es null)");
        }
      } catch (e) {
        // Ignorar error 404 (sin suscripción)
        console.log("Usuario sin suscripción activa (error):", e.message);
      }
      
    } finally {
      loading = false;
    }
  });

  async function handleSubscribe(planId) {
    try {
      processingPayment = true;
      // Camino B: Suscripción real (Preapproval)
      const res = await paymentAPI.createSubscription(planId);
      
      if (res.success && res.init_point) {
        window.location.href = res.init_point;
      } else {
        alert('Error al iniciar el pago: ' + (res.message || 'Intente nuevamente'));
      }
    } catch (e) {
      console.error(e);
      alert(e?.message || 'Error al procesar la suscripción');
    } finally {
      processingPayment = false;
    }
  }

  function openCancelModal() {
    if (!mySubscription) return;
    showCancelModal = true;
  }

  function closeCancelModal() {
    showCancelModal = false;
  }

  async function confirmCancelSubscription() {
    if (!mySubscription) return;
    try {
      processingPayment = true;
      const res = await paymentAPI.cancelSubscription();
      if (res.success) {
        toasts.push('Suscripción cancelada.', { type: 'success' });
        // Refrescar estado
        const subRes = await suscripcionesAPI.getMiSuscripcion();
        mySubscription = subRes.success ? subRes.data : null;
        showCancelModal = false;
      } else {
        toasts.push(res.message || 'No se pudo cancelar la suscripción', { type: 'error' });
      }
    } catch (e) {
      console.error(e);
      toasts.push(e?.message || 'Error al cancelar la suscripción', { type: 'error' });
    } finally {
      processingPayment = false;
    }
  }

  function formatCurrency(amount) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(amount);
  }
</script>

<div class="container mx-auto px-6 py-8">
  <h3 class="text-gray-700 text-3xl font-medium mb-6">Suscripciones</h3>

  <Modal
    isOpen={showCancelModal}
    title="Cancelar suscripción"
    onClose={closeCancelModal}
    size="sm"
  >
    <p class="text-gray-700">
      ¿Querés cancelar tu suscripción? Vas a perder el acceso al servicio al finalizar el período actual.
    </p>

    <div slot="footer">
      <button
        on:click={closeCancelModal}
        class="px-4 py-2 font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        disabled={processingPayment}
      >
        Volver
      </button>
      <button
        on:click={confirmCancelSubscription}
        class="px-4 py-2 font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
        disabled={processingPayment}
      >
        {#if processingPayment}
          Cancelando...
        {:else}
          Confirmar cancelación
        {/if}
      </button>
    </div>
  </Modal>

  {#if loading}
    <div class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
    </div>
  {:else if error}
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <span class="block sm:inline">{error}</span>
    </div>
  {:else}
    <!-- Debug info (temporal) -->
    {#if import.meta.env.DEV}
      <div class="mb-4 p-4 bg-gray-100 text-xs rounded">
        <p><strong>Debug:</strong></p>
        <p>Planes cargados: {plans.length}</p>
        <p>Usuario: {currentUser ? currentUser.name : 'No cargado'}</p>
        <p>Plan del usuario: {currentUser?.plan || 'N/A'}</p>
        <p>Plan seleccionado encontrado: {selectedPlan ? selectedPlan.name : 'No encontrado'}</p>
        <p>Suscripción activa: {mySubscription ? 'Sí' : 'No'}</p>
      </div>
    {/if}
    
    <!-- Suscripción Actual -->
    {#if mySubscription}
      <div class="mb-10">
        <h4 class="text-xl font-semibold text-gray-600 mb-4">Mi Suscripción Actual</h4>
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-green-500">
          <div class="p-6">
            <div class="flex justify-between items-start">
              <div>
                <h5 class="text-2xl font-bold text-gray-800">{mySubscription.planName}</h5>
                <p class="text-gray-600 mt-1">
                  Estado: <Badge type="success">{mySubscription.status.toUpperCase()}</Badge>
                </p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-green-600">{formatCurrency(mySubscription.price)}</p>
                <p class="text-sm text-gray-500">/{mySubscription.frequency}</p>
              </div>
            </div>
            
            <div class="mt-6 border-t pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">Fecha de inicio</p>
                <p class="font-medium">{mySubscription.startDate}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Próximo cobro</p>
                <p class="font-medium">{mySubscription.nextBillingDate}</p>
              </div>
              <div class="md:col-span-2">
                <p class="text-sm text-gray-500">ID externo (Mercado Pago)</p>
                <p class="font-mono text-sm break-all">{mySubscription.externalPaymentId || '—'}</p>
              </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3">
              <button
                on:click={openCancelModal}
                disabled={processingPayment}
                class="px-6 py-2 font-semibold text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {#if processingPayment}
                  Procesando...
                {:else}
                  Cancelar suscripción
                {/if}
              </button>
            </div>
          </div>
        </div>
      </div>
    {:else if selectedPlan}
      <!-- Plan Seleccionado para Pagar -->
      <div class="mb-10">
        <h4 class="text-xl font-semibold text-gray-600 mb-4">Tu Plan Seleccionado</h4>
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-blue-500">
          <div class="p-6">
            <div class="flex justify-between items-start">
              <div>
                <h5 class="text-2xl font-bold text-gray-800">{selectedPlan.name}</h5>
                <p class="text-gray-600 mt-1">
                  Estado: <Badge type="warning">PENDIENTE DE PAGO</Badge>
                </p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-green-600">{formatCurrency(selectedPlan.price)}</p>
                <p class="text-sm text-gray-500">/{selectedPlan.frequency}</p>
              </div>
            </div>
            
            {#if selectedPlan.description}
              <p class="text-gray-600 text-sm mt-4">{selectedPlan.description}</p>
            {/if}
            
            <div class="mt-6 border-t pt-4">
              <ul class="space-y-2 text-sm text-gray-600 mb-6">
                {#if selectedPlan.features && selectedPlan.features.length > 0}
                  {#each (typeof selectedPlan.features === 'string' ? JSON.parse(selectedPlan.features) : selectedPlan.features) as feature}
                    <li class="flex items-center">
                      <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                      {feature}
                    </li>
                  {/each}
                {:else}
                  {#if selectedPlan.visitsPerMonth}
                    <li class="flex items-center">
                      <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                      {selectedPlan.visitsPerMonth} visitas al mes
                    </li>
                  {/if}
                  <li class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Mantenimiento completo
                  </li>
                {/if}
              </ul>
              
              <button
                on:click={() => handleSubscribe(selectedPlan.id)}
                disabled={processingPayment}
                class="w-full px-6 py-3 font-semibold text-white transition-colors duration-150 bg-green-600 border border-transparent rounded-lg active:bg-green-600 hover:bg-green-700 focus:outline-none focus:shadow-outline-purple disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {#if processingPayment}
                  Procesando...
                {:else}
                  Pagar Ahora
                {/if}
              </button>
            </div>
          </div>
        </div>
      </div>
    {/if}

    <!-- Planes Disponibles (oculto cuando tiene plan pendiente de pago) -->
    {#if !selectedPlan && plans.length > 0}
      <h4 class="text-xl font-semibold text-gray-600 mb-4">
        {mySubscription ? 'Cambiar Plan' : 'Elegir un Plan'}
      </h4>
      
      <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">
        {#each plans as plan}
        <div class="flex flex-col p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 border border-gray-100 relative overflow-hidden">
          {#if plan.name.includes('Premium') || plan.name.includes('Anual')}
            <div class="absolute top-0 right-0 bg-yellow-400 text-xs font-bold px-3 py-1 rounded-bl-lg">
              RECOMENDADO
            </div>
          {/if}
          
          <h3 class="text-lg font-bold text-gray-700 mb-2">{plan.name}</h3>
          
          <div class="my-4">
            <span class="text-4xl font-bold text-gray-800">{formatCurrency(plan.price)}</span>
            <span class="text-gray-500">/{plan.frequency}</span>
          </div>
          
          <p class="text-gray-600 text-sm mb-6 flex-grow">{plan.description || 'Plan completo para el cuidado de tu jardín.'}</p>
          
          <ul class="mb-6 space-y-2 text-sm text-gray-600">
            {#if plan.features}
              {#each (typeof plan.features === 'string' ? JSON.parse(plan.features) : plan.features) as feature}
                <li class="flex items-center">
                  <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  {feature}
                </li>
              {/each}
            {:else}
              <li class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {plan.visitsPerMonth} visitas al mes
              </li>
              <li class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Mantenimiento completo
              </li>
            {/if}
          </ul>
          
          <button
            on:click={() => handleSubscribe(plan.id)}
            disabled={processingPayment || (mySubscription && mySubscription.subscriptionId === plan.id)}
            class="w-full px-4 py-2 font-medium text-white transition-colors duration-150 bg-green-600 border border-transparent rounded-lg active:bg-green-600 hover:bg-green-700 focus:outline-none focus:shadow-outline-purple disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {#if processingPayment}
              Procesando...
            {:else if mySubscription && mySubscription.subscriptionId === plan.id}
              Plan Actual
            {:else}
              {mySubscription ? 'Cambiar a este Plan' : 'Suscribirme'}
            {/if}
          </button>
        </div>
        {/each}
      </div>
    {:else if !selectedPlan}
      <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">No hay planes disponibles en este momento.</span>
      </div>
    {/if}
  {/if}
</div>
