<script>
  import Card from '../../../components/Card.svelte';
  import { Link } from 'svelte-routing';
</script>

<div class="max-w-4xl mx-auto py-8 px-4">
  <Card title="Pago Pendiente">
    <div class="text-center py-8">
      <div class="text-yellow-500 text-6xl mb-4 mx-auto flex justify-center">
        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold mb-2 text-gray-800">Pago en proceso</h2>
      <p class="text-gray-600 mb-6">Tu pago se está procesando. Te notificaremos cuando se confirme.</p>
      
      <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-6 max-w-lg mx-auto">
        <p class="text-sm text-yellow-800">
          Si pagaste en efectivo (Rapipago/PagoFácil), la acreditación puede demorar hasta 48hs hábiles.
        </p>
      </div>

      <div>
        <Link 
          to="/dashboard" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200 inline-block"
        >
          Ir al Dashboard
        </Link>
      </div>
    </div>
  </Card>
</div>
