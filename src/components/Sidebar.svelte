<script>
  import { Link } from 'svelte-routing';
  import { auth } from '../stores/auth';
  
  export let isOpen = false;
  
  let currentUser;
  let userRole;
  
  auth.subscribe(value => {
    currentUser = value.user;
    userRole = value.role;
  });
  
  function closeSidebar() {
    isOpen = false;
  }
</script>

<!-- Sidebar backdrop para móvil -->
{#if isOpen}
  <div 
    class="fixed inset-0 z-10 bg-black bg-opacity-50 lg:hidden"
    on:click={closeSidebar}
  ></div>
{/if}

<!-- Sidebar -->
<aside 
  class="fixed inset-y-0 left-0 z-20 flex-shrink-0 w-64 overflow-y-auto bg-white border-r border-gray-200 lg:static lg:block {isOpen ? 'block' : 'hidden'}"
>
  <div class="py-4 text-gray-500">
    <!-- Logo -->
    <a href="/" class="ml-6 text-2xl font-bold text-primary-600">
      Cesped365
    </a>

    <!-- Navigation -->
    <ul class="mt-8">
      <!-- Resumen (solo admin) / Reporte de mi jardín (clientes) -->
      {#if userRole === 'admin'}
        <li class="relative px-6 py-3">
          <Link 
            to="/dashboard/resumen" 
            class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-primary-600"
            on:click={closeSidebar}
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="ml-4">Resumen</span>
          </Link>
        </li>
      {:else}
        <li class="relative px-6 py-3">
          <Link 
            to="/dashboard/mi-jardin" 
            class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-primary-600"
            on:click={closeSidebar}
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="ml-4">Reporte de mi Jardín</span>
          </Link>
        </li>
      {/if}

      <!-- Reportes -->
      <li class="relative px-6 py-3">
        <Link 
          to="/dashboard/reportes" 
          class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-primary-600"
          on:click={closeSidebar}
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span class="ml-4">Reportes</span>
        </Link>
      </li>

      <!-- Historial -->
      <li class="relative px-6 py-3">
        <Link 
          to="/dashboard/historial" 
          class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-primary-600"
          on:click={closeSidebar}
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="ml-4">Historial</span>
        </Link>
      </li>

      <!-- Clientes (solo admin) -->
      {#if userRole === 'admin'}
        <li class="relative px-6 py-3">
          <Link 
            to="/dashboard/clientes" 
            class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-primary-600"
            on:click={closeSidebar}
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="ml-4">Clientes</span>
          </Link>
        </li>
        
        <!-- Agenda (solo admin) -->
        <li class="relative px-6 py-3">
          <Link 
            to="/dashboard/agenda" 
            class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-primary-600"
            on:click={closeSidebar}
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="ml-4">Agenda</span>
          </Link>
        </li>
      {/if}

      <!-- Perfil -->
      <li class="relative px-6 py-3">
        <Link 
          to="/dashboard/perfil" 
          class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-primary-600"
          on:click={closeSidebar}
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="ml-4">Perfil</span>
        </Link>
      </li>
    </ul>
  </div>
</aside>
