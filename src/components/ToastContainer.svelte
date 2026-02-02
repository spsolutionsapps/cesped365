<script>
  import { fly } from 'svelte/transition';
  import { toasts } from '../stores/toasts';

  const stylesByType = {
    success: 'bg-green-600 text-white',
    error: 'bg-red-600 text-white',
    warning: 'bg-yellow-500 text-black',
    info: 'bg-blue-600 text-white',
  };
</script>

<div class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 w-[min(92vw,380px)]">
  {#each $toasts as toast (toast.id)}
    <div
      class="rounded-lg shadow-lg overflow-hidden {stylesByType[toast.type] || stylesByType.info}"
      in:fly={{ x: 24, duration: 180 }}
      out:fly={{ x: 24, duration: 180 }}
      role="status"
      aria-live="polite"
    >
      <div class="flex items-start gap-3 px-4 py-3">
        <div class="flex-1 text-sm font-medium leading-5">
          {toast.message}
        </div>
        <button
          class="opacity-90 hover:opacity-100 transition-opacity"
          aria-label="Cerrar"
          on:click={() => toasts.remove(toast.id)}
          type="button"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  {/each}
</div>

