<script>
  export let isOpen = false;
  export let title = '';
  export let size = 'md'; // sm, md, lg, xl
  export let onClose = () => {};

  const sizeClasses = {
    sm: 'max-w-md',
    md: 'max-w-2xl',
    lg: 'max-w-4xl',
    xl: 'max-w-6xl'
  };

  function handleBackdropClick(e) {
    if (e.target === e.currentTarget) {
      onClose();
    }
  }

  function handleEscape(e) {
    if (e.key === 'Escape' && isOpen) {
      onClose();
    }
  }
</script>

<svelte:window on:keydown={handleEscape} />

{#if isOpen}
  <div 
    class="fixed inset-0 z-50 overflow-y-auto"
    on:click={handleBackdropClick}
    role="dialog"
    aria-modal="true"
  >
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    
    <!-- Modal Container -->
    <div class="flex min-h-screen items-center justify-center p-4">
      <!-- Modal Content -->
      <div class="relative w-full {sizeClasses[size]} bg-white rounded-lg shadow-xl transform transition-all">
        <!-- Header -->
        {#if title || $$slots.header}
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <slot name="header">
              <h3 class="text-xl font-semibold text-gray-900">{title}</h3>
            </slot>
            <button
              on:click={onClose}
              class="text-gray-400 hover:text-gray-600 transition-colors"
              aria-label="Cerrar"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        {/if}

        <!-- Body -->
        <div class="p-6">
          <slot />
        </div>

        <!-- Footer -->
        {#if $$slots.footer}
          <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
            <slot name="footer" />
          </div>
        {/if}
      </div>
    </div>
  </div>
{/if}

