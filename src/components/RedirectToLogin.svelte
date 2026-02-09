<script>
  import { onMount } from 'svelte';
  import { navigate } from 'svelte-routing';

  onMount(() => {
    const currentPath = window.location.pathname + window.location.search;
    const redirect = currentPath && currentPath !== '/login' ? '?redirect=' + encodeURIComponent(window.location.pathname) : '';
    navigate('/login' + redirect, { replace: true });
  });
</script>

<div class="flex justify-center items-center h-screen bg-gray-50">
  <p class="text-gray-600">Redirigiendo a login...</p>
</div>
