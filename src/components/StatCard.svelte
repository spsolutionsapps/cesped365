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

<div class="bg-white rounded-lg shadow-md p-6">
  <div class="flex items-center">
    <div class="flex-shrink-0">
      <div class="p-3 rounded-full {colorClasses[color]}">
        {@html icon}
      </div>
    </div>
    <div class="ml-5 w-0 flex-1">
      <dl>
        <dt class="text-sm font-medium text-gray-500 truncate">
          {title}
        </dt>
        <dd class="text-3xl font-bold text-gray-900">
          {value}
        </dd>
      </dl>
    </div>
  </div>
</div>
