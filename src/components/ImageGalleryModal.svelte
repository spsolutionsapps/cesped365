<script>
  export let isOpen = false;
  export let images = [];
  export let initialIndex = 0;
  export let onClose = () => {};

  let currentIndex = initialIndex;

  $: if (isOpen) {
    currentIndex = initialIndex;
  }

  function nextImage() {
    currentIndex = (currentIndex + 1) % images.length;
  }

  function prevImage() {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
  }

  function handleKeydown(e) {
    if (!isOpen) return;
    if (e.key === 'Escape') onClose();
    if (e.key === 'ArrowRight') nextImage();
    if (e.key === 'ArrowLeft') prevImage();
  }
</script>

<svelte:window on:keydown={handleKeydown} />

{#if isOpen}
  <div class="fixed inset-0 z-50 overflow-y-auto" on:click={onClose}>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Overlay -->
      <div class="fixed inset-0 transition-opacity bg-black bg-opacity-90"></div>

      <!-- Modal -->
      <div 
        class="inline-block align-middle w-full max-w-6xl text-left overflow-hidden transform transition-all"
        on:click|stopPropagation
      >
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <span class="text-white text-lg">
            Imagen {currentIndex + 1} de {images.length}
          </span>
          <button
            on:click={onClose}
            class="text-white hover:text-gray-300 p-2"
          >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Image -->
        <div class="relative">
          <img 
            src={images[currentIndex]} 
            alt="Imagen {currentIndex + 1}"
            class="w-full h-auto max-h-[80vh] object-contain mx-auto"
          />

          <!-- Navigation buttons -->
          {#if images.length > 1}
            <button
              on:click={prevImage}
              class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-75 transition-all"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <button
              on:click={nextImage}
              class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-75 transition-all"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          {/if}
        </div>

        <!-- Thumbnails -->
        {#if images.length > 1}
          <div class="flex gap-2 mt-4 overflow-x-auto pb-2">
            {#each images as image, index}
              <button
                on:click={() => currentIndex = index}
                class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all {currentIndex === index ? 'border-primary-500' : 'border-transparent opacity-60 hover:opacity-100'}"
              >
                <img 
                  src={image} 
                  alt="Miniatura {index + 1}"
                  class="w-full h-full object-cover"
                />
              </button>
            {/each}
          </div>
        {/if}
      </div>
    </div>
  </div>
{/if}
