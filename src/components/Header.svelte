<script>
  import { navigate } from 'svelte-routing';
  import { auth } from '../stores/auth';
  
  export let toggleSidebar;
  
  let currentUser;
  let userRole;
  let showDropdown = false;
  
  auth.subscribe(value => {
    currentUser = value.user;
    userRole = value.role;
  });
  
  function handleLogout() {
    auth.logout();
    navigate('/login', { replace: true });
  }
  
  function toggleDropdown() {
    showDropdown = !showDropdown;
  }
</script>

<header class="z-10 py-4 bg-white">
  <div class="container flex items-center justify-between h-full px-6 mx-auto text-primary-600">
    <!-- Mobile hamburger -->
    <button
      class="p-1 mr-5 -ml-1 rounded-md lg:hidden focus:outline-none focus:shadow-outline-primary"
      on:click={toggleSidebar}
      aria-label="Menu"
    >
      <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
      </svg>
    </button>

    <div class="flex-1"></div>

    <ul class="flex items-center flex-shrink-0 space-x-6">


      <!-- Profile menu -->
      <li class="relative">
        <button
          class="align-middle rounded-full focus:shadow-outline-primary focus:outline-none"
          on:click={toggleDropdown}
          aria-label="Cuenta"
        >
          <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-semibold">
            {currentUser?.name?.charAt(0) || 'U'}
          </div>
        </button>

        {#if showDropdown}
          <div class="absolute right-0 w-56 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg">
            <div class="px-4 py-3 border-b border-gray-200">
              <p class="text-sm font-semibold text-gray-900">{currentUser?.name}</p>
              <p class="text-xs text-gray-600">{currentUser?.email}</p>
              {#if userRole === 'admin'}
                <span class="inline-block px-2 py-1 mt-2 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">
                  Administrador
                </span>
              {/if}
            </div>
            <ul class="py-2">
              <li>
                <a
                  href="/dashboard/perfil"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  on:click={() => showDropdown = false}
                >
                  Perfil
                </a>
              </li>
              <li>
                <button
                  on:click={handleLogout}
                  class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                >
                  Cerrar sesión
                </button>
              </li>
            </ul>
          </div>
        {/if}
      </li>
    </ul>
  </div>
</header>
