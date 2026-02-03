<script>
  import Card from '../../../components/Card.svelte';
  import { Link } from 'svelte-routing';
</script>

<div class="max-w-4xl mx-auto py-8 px-4">
  <Card title="Pago Fallido">
    <div class="text-center py-8">
      <div class="text-red-500 text-6xl mb-4 mx-auto flex justify-center">
        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold mb-2 text-gray-800">Hubo un problema con tu pago</h2>
      <p class="text-gray-600 mb-6">No se pudo procesar la transacción. Por favor, intenta nuevamente.</p>
      
      <div>
        <Link 
          to="/dashboard/suscripciones" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200 inline-block mr-4"
        >
          Intentar nuevamente
        </Link>
        <Link 
          to="/dashboard" 
          class="text-gray-600 hover:text-gray-800 font-medium py-2 px-4 rounded transition duration-200 inline-block"
        >
          Volver al Dashboard
        </Link>
      </div>
    </div>
  </Card>
</div>
