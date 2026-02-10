<script>
  export let title = '';
  export let value = '';
  export let icon = '';
  export let color = 'primary';
  
  const colorClasses = {
    primary: 'bg-primary-100 text-primary-600',
    green: 'bg-green-100 text-green-600',
    blue: 'bg-blue-100 text-blue-600',
    orange: 'bg-orange-100 text-orange-600',
    red: 'bg-red-100 text-red-600',
  };
</script>

<div class="bg-white rounded-lg p-4 md:p-6">
  <div class="flex items-center">
    <div class="flex-shrink-0">
      <div class="p-2 rounded-full md:p-3 {colorClasses[color]} [&_svg]:w-4 [&_svg]:h-4 md:[&_svg]:w-6 md:[&_svg]:h-6">
        {@html icon}
      </div>
    </div>
    <div class="ml-3 md:ml-5 w-0 flex-1 min-w-0">
      <dl class="flex justify-between items-center gap-2 md:flex-col md:items-stretch md:gap-0">
        <dt class="text-xs md:text-sm font-medium text-gray-500 truncate">
          {title}
        </dt>
        <dd class="text-lg font-bold text-gray-900 shrink-0 md:text-3xl md:mt-1">
          {value}
        </dd>
      </dl>
    </div>
  </div>
</div>
