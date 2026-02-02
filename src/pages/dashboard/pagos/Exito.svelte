<script>
  import Card from '../../../components/Card.svelte';
  import { Link } from 'svelte-routing';

  const urlParams = new URLSearchParams(window.location.search);
  const paymentId = urlParams.get('payment_id');
</script>

<div class="max-w-4xl mx-auto py-8 px-4">
  <Card title="¡Pago Exitoso!">
    <div class="text-center py-8">
      <div class="text-green-500 text-6xl mb-4 mx-auto flex justify-center">
        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold mb-2 text-gray-800">¡Gracias por tu suscripción!</h2>
      <p class="text-gray-600 mb-6">Tu pago ha sido procesado correctamente y tu suscripción está activa.</p>
      
      {#if paymentId}
        <div class="bg-gray-100 p-4 rounded-lg inline-block mb-6">
          <p class="text-sm text-gray-500">ID de transacción: <span class="font-mono font-bold">{paymentId}</span></p>
        </div>
      {/if}

      <div>
        <Link 
          to="/dashboard" 
          class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200 inline-block"
        >
          Volver al Dashboard
        </Link>
      </div>
    </div>
  </Card>
</div>
