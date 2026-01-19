<script>
  import { onMount } from 'svelte';
  import { navigate } from 'svelte-routing';
  import { auth } from '../stores/auth';
  import Sidebar from '../components/Sidebar.svelte';
  import Header from '../components/Header.svelte';
  
  let isAuthenticated = false;
  let isChecking = true;
  let sidebarOpen = false;
  
  auth.subscribe(value => {
    isAuthenticated = value.isAuthenticated;
    isChecking = value.isChecking;
  });
  
  $: if (!isChecking && !isAuthenticated) {
    navigate('/login', { replace: true });
  }
  
  function toggleSidebar() {
    sidebarOpen = !sidebarOpen;
  }
</script>

{#if isChecking}
  <div class="flex items-center justify-center h-screen bg-gray-50">
    <div class="text-center">
      <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-primary-600 mx-auto"></div>
      <p class="mt-4 text-gray-600 font-medium">Verificando sesión...</p>
    </div>
  </div>
{:else if isAuthenticated}
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar bind:isOpen={sidebarOpen} />

    <!-- Main content -->
    <div class="flex flex-col flex-1 w-full overflow-hidden">
      <!-- Header -->
      <Header {toggleSidebar} />

      <!-- Main content area -->
      <main class="h-full overflow-y-auto">
        <div class="container px-6 mx-auto grid">
          <slot />
        </div>
      </main>
    </div>
  </div>
{/if}
