<script>
  export let title = '';
  export let className = '';
</script>

<div class="bg-white rounded-lg shadow-md overflow-hidden {className}">
  {#if title}
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">{title}</h3>
    </div>
  {/if}
  <div class="p-6">
    <slot />
  </div>
</div>
