<script>
  import { onMount } from 'svelte';
  import { clientesAPI } from '../../../services/api';
  import Card from '../../../components/Card.svelte';
  import Badge from '../../../components/Badge.svelte';
  import ClienteModal from '../../../components/ClienteModal.svelte';
  
  let clientes = [];
  let searchTerm = '';
  let selectedPlan = 'Todos los planes';
  let selectedEstado = 'Todos los estados';
  let loading = true;
  let error = null;
  let selectedCliente = null;
  let showClienteModal = false;
  let clienteToEdit = null;
  let showDetailModal = false;
  let clienteDetail = null;
  let expandedClients = new Set();
  
  onMount(async () => {
    await loadClientes();
  });
  
  async function loadClientes() {
    try {
      loading = true;
      const response = await clientesAPI.getAll();
      if (response.success) {
        clientes = response.data;
      }
      loading = false;
    } catch (err) {
      console.error('Error cargando clientes:', err);
      error = 'Error al cargar los clientes. Verifica que el backend esté corriendo.';
      loading = false;
    }
  }
  
  $: filteredClientes = clientes.filter(cliente => {
    // Filtro de búsqueda
    const matchesSearch = searchTerm === '' || 
      cliente.nombre.toLowerCase().includes(searchTerm.toLowerCase()) ||
      cliente.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
      cliente.direccion.toLowerCase().includes(searchTerm.toLowerCase());
    
    // Filtro de plan
    const matchesPlan = selectedPlan === 'Todos los planes' || cliente.plan === selectedPlan;
    
    // Filtro de estado
    const matchesEstado = selectedEstado === 'Todos los estados' || cliente.estado === selectedEstado;
    
    return matchesSearch && matchesPlan && matchesEstado;
  });
  
function getBadgeType(estado) {
    switch(estado) {
      case 'Activo': return 'success';
      case 'Pendiente': return 'warning';
      case 'Cancelada': return 'danger';
      case 'Pausada': return 'warning';
      case 'Inactivo': return 'danger';
      default: return 'default';
    }
  }

  function toggleClientExpand(clienteId) {
    if (expandedClients.has(clienteId)) {
      expandedClients.delete(clienteId);
    } else {
      expandedClients.add(clienteId);
    }
    expandedClients = expandedClients; // Trigger reactivity
  }
  
  function openCreateModal() {
    clienteToEdit = null;
    showClienteModal = true;
  }
  
  function openEditModal(cliente) {
    clienteToEdit = cliente;
    showClienteModal = true;
  }
  
  async function viewCliente(cliente) {
    try {
      const response = await clientesAPI.getById(cliente.id);
      if (response.success) {
        clienteDetail = response.data;
        showDetailModal = true;
      }
    } catch (err) {
      console.error('Error cargando detalle del cliente:', err);
      alert('Error al cargar el detalle del cliente');
    }
  }
  
  async function deleteCliente(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este cliente? Esta acción no se puede deshacer.')) {
      return;
    }

    try {
      const response = await clientesAPI.delete(id);
      if (response.success) {
        await loadClientes();
      }
    } catch (err) {
      console.error('Error eliminando cliente:', err);
      alert('Error al eliminar el cliente');
    }
  }
  
  function closeModal() {
    selectedCliente = null;
    showDetailModal = false;
    clienteDetail = null;
  }

  function handleBackdropClick(event) {
    if (event.target === event.currentTarget) {
      closeModal();
    }
  }
  
  async function handleClienteSaved() {
    showClienteModal = false;
    clienteToEdit = null;
    await loadClientes();
  }

  function handleKeydown(event) {
    if (event.key === 'Escape' && showDetailModal) {
      closeModal();
    }
  }
</script>

<svelte:window on:keydown={handleKeydown} />

<div class="py-6">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-gray-700">
      Gestión de Clientes
    </h2>
    <button
      on:click={openCreateModal}
      class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 font-medium flex items-center gap-2"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Nuevo Cliente
    </button>
  </div>

  <!-- Búsqueda y filtros -->
  <Card class="mb-6">
    <div class="flex flex-col lg:flex-row gap-4">
      <div class="flex-1 w-full">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            type="text"
            bind:value={searchTerm}
            placeholder="Buscar por nombre, email o dirección..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          />
        </div>
      </div>
      <select bind:value={selectedPlan} class="w-full lg:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
        <option>Todos los planes</option>
        <option>Urbano</option>
        <option>Residencial</option>
        <option>Parque</option>
      </select>
      <select bind:value={selectedEstado} class="w-full lg:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
        <option>Todos los estados</option>
        <option>Activo</option>
        <option>Pendiente</option>
        <option>Cancelada</option>
        <option>Pausada</option>
        <option>Inactivo</option>
      </select>
    </div>
  </Card>

  <!-- Tabla de clientes (Desktop) -->
  <div class="hidden md:block">
    <Card>
      <div class="overflow-x-auto">
      <table class="w-full whitespace-no-wrap">
        <thead>
          <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
            <th class="px-4 py-3">Cliente</th>
            <th class="px-4 py-3">Contacto</th>
            <th class="px-4 py-3">Dirección</th>
            <th class="px-4 py-3">Plan</th>
            <th class="px-4 py-3">Estado</th>
            <th class="px-4 py-3">Referido por</th>
            <th class="px-4 py-3">Última Visita</th>
            <th class="px-4 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y">
          {#each filteredClientes as cliente}
            <tr class="text-gray-700">
              <td class="px-4 py-3">
                <div class="flex items-center text-sm">
                  <div class="relative w-10 h-10 mr-3 rounded-full bg-primary-100 flex items-center justify-center">
                    <span class="font-semibold text-primary-600">
                      {cliente.nombre.split(' ').map(n => n[0]).join('')}
                    </span>
                  </div>
                  <div>
                    <p class="font-semibold">{cliente.nombre}</p>
                    <p class="text-xs text-gray-600">ID: {cliente.id}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-sm">
                <div>
                  <p class="text-gray-900">{cliente.email}</p>
                  <p class="text-xs text-gray-600">{cliente.telefono}</p>
                </div>
              </td>
              <td class="px-4 py-3 text-sm">
                {cliente.direccion}
              </td>
              <td class="px-4 py-3 text-sm">
                <Badge type={cliente.plan === 'Premium' ? 'info' : 'default'}>
                  {cliente.plan}
                </Badge>
              </td>
              <td class="px-4 py-3 text-sm">
                <Badge type={getBadgeType(cliente.estado)}>
                  {cliente.estado}
                </Badge>
              </td>
              <td class="px-4 py-3 text-sm">
                <span class="text-gray-900">
                  {cliente.referidoPor || '-'}
                </span>
              </td>
              <td class="px-4 py-3 text-sm">
                <div>
                  <p class="text-gray-900">
                    {cliente.ultimaVisita ? new Date(cliente.ultimaVisita).toLocaleDateString('es-AR') : '-'}
                  </p>
                  <p class="text-xs text-gray-600">
                    Próxima: {cliente.proximaVisita ? new Date(cliente.proximaVisita).toLocaleDateString('es-AR') : '-'}
                  </p>
                </div>
              </td>
              <td class="px-4 py-3 text-sm">
                <div class="flex items-center space-x-2">
                  <button
                    on:click={() => viewCliente(cliente)}
                    class="text-primary-600 hover:text-primary-700"
                    title="Ver detalles"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button
                    on:click={() => openEditModal(cliente)}
                    class="text-blue-600 hover:text-blue-700"
                    title="Editar"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    on:click={() => deleteCliente(cliente.id)}
                    class="text-red-600 hover:text-red-700"
                    title="Eliminar"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
      </div>
    </Card>
  </div>

  <!-- Lista de clientes mobile (collapsable) -->
  <div class="block md:hidden">
    <Card>
      <div class="space-y-3">
      {#each filteredClientes as cliente}
        <div class="border border-gray-200 rounded-lg overflow-hidden">
          <!-- Header del collapsable -->
          <button
            on:click={() => toggleClientExpand(cliente.id)}
            class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors flex items-center justify-between text-left"
          >
            <div class="flex items-center">
              <div class="relative w-10 h-10 mr-3 rounded-full bg-primary-100 flex items-center justify-center">
                <span class="font-semibold text-primary-600">
                  {cliente.nombre.split(' ').map(n => n[0]).join('')}
                </span>
              </div>
              <div>
                <p class="font-semibold text-gray-900">{cliente.nombre}</p>
                <p class="text-xs text-gray-600">ID: {cliente.id}</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <Badge type={getBadgeType(cliente.estado)} class="text-xs">
                {cliente.estado}
              </Badge>
              <svg 
                class="w-4 h-4 text-gray-500 transition-transform duration-200 {expandedClients.has(cliente.id) ? 'rotate-180' : ''}" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </button>

          <!-- Contenido collapsable -->
          {#if expandedClients.has(cliente.id)}
            <div class="px-4 py-4 bg-white border-t border-gray-200 space-y-3">
              <!-- Contacto -->
              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Contacto</p>
                <p class="text-sm text-gray-900">{cliente.email}</p>
                <p class="text-sm text-gray-600">{cliente.telefono}</p>
              </div>

              <!-- Dirección -->
              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Dirección</p>
                <p class="text-sm text-gray-900">{cliente.direccion}</p>
              </div>

              <!-- Plan y Referido -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Plan</p>
                  <Badge type={cliente.plan === 'Premium' ? 'info' : 'default'}>
                    {cliente.plan}
                  </Badge>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Referido por</p>
                  <p class="text-sm text-gray-900">{cliente.referidoPor || '-'}</p>
                </div>
              </div>

              <!-- Visitas -->
              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Visitas</p>
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <p class="text-gray-500 text-xs">Última:</p>
                    <p class="text-gray-900">
                      {cliente.ultimaVisita ? new Date(cliente.ultimaVisita).toLocaleDateString('es-AR') : '-'}
                    </p>
                  </div>
                  <div>
                    <p class="text-gray-500 text-xs">Próxima:</p>
                    <p class="text-gray-900">
                      {cliente.proximaVisita ? new Date(cliente.proximaVisita).toLocaleDateString('es-AR') : '-'}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Acciones -->
              <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Acciones</p>
                <div class="flex space-x-3">
                  <button
                    on:click={() => viewCliente(cliente)}
                    class="flex items-center px-3 py-2 text-xs font-medium text-primary-600 bg-primary-50 rounded-md hover:bg-primary-100"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Ver
                  </button>
                  <button
                    on:click={() => openEditModal(cliente)}
                    class="flex items-center px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar
                  </button>
                  <button
                    on:click={() => deleteCliente(cliente.id)}
                    class="flex items-center px-3 py-2 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                  </button>
                </div>
              </div>
            </div>
          {/if}
        </div>
      {/each}
      </div>
    </Card>
  </div>

  <!-- Pagination (mobile) -->
  <div class="block md:hidden">
    <Card>
      <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Mostrando <span class="font-semibold">{filteredClientes.length}</span> de <span class="font-semibold">{clientes.length}</span> clientes
        </div>
        <div class="flex space-x-2">
          <button class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50" disabled>
            Anterior
          </button>
          <button class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50" disabled>
            Siguiente
          </button>
        </div>
      </div>
      </div>
    </Card>
  </div>

  <!-- Estadísticas rápidas -->
  <div class="grid gap-6 mt-8 md:grid-cols-3 lg:grid-cols-6">
    <Card>
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-primary-100 text-primary-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Total</p>
          <p class="text-2xl font-bold text-gray-900">{clientes.length}</p>
        </div>
      </div>
    </Card>

    <Card>
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Activos</p>
          <p class="text-2xl font-bold text-gray-900">
            {clientes.filter(c => c.estado === 'Activo').length}
          </p>
        </div>
      </div>
    </Card>

    <Card>
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-orange-100 text-orange-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Pendientes</p>
          <p class="text-2xl font-bold text-gray-900">
            {clientes.filter(c => c.estado === 'Pendiente').length}
          </p>
        </div>
      </div>
    </Card>

    <Card>
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Urbano</p>
          <p class="text-2xl font-bold text-gray-900">
            {clientes.filter(c => c.plan === 'Urbano').length}
          </p>
        </div>
      </div>
    </Card>

    <Card>
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Residencial</p>
          <p class="text-2xl font-bold text-gray-900">
            {clientes.filter(c => c.plan === 'Residencial').length}
          </p>
        </div>
      </div>
    </Card>

    <Card>
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-teal-100 text-teal-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Parque</p>
          <p class="text-2xl font-bold text-gray-900">
            {clientes.filter(c => c.plan === 'Parque').length}
          </p>
        </div>
      </div>
    </Card>
  </div>
</div>

<!-- Modal de Crear/Editar Cliente -->
<ClienteModal
  isOpen={showClienteModal}
  cliente={clienteToEdit}
  onClose={() => {
    showClienteModal = false;
    clienteToEdit = null;
  }}
  onSuccess={handleClienteSaved}
/>

<!-- Modal de detalles del cliente -->
{#if showDetailModal && clienteDetail}
  <div 
    class="fixed inset-0 z-50 overflow-y-auto" 
    on:click={handleBackdropClick}
    on:keydown={(e) => (e.key === 'Enter' || e.key === ' ') && closeModal()}
    role="button"
    tabindex="0"
    aria-label="Cerrar modal"
  >
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

      <div 
        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
      >
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <!-- Header -->
          <div class="flex justify-between items-start mb-6">
            <div>
              <h3 id="modal-title" class="text-2xl font-bold text-gray-900">{clienteDetail.nombre}</h3>
              <p class="text-sm text-gray-500 mt-1">Cliente ID: {clienteDetail.id}</p>
            </div>
            <button
              on:click={closeModal}
              class="text-gray-400 hover:text-gray-500"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Content -->
          <div class="space-y-6">
            <!-- Información de contacto -->
            <div>
              <h4 class="font-semibold text-gray-900 mb-3">Información de Contacto</h4>
              <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Email:</span>
                  <span class="text-sm font-medium text-gray-900">{clienteDetail.email}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Teléfono:</span>
                  <span class="text-sm font-medium text-gray-900">{clienteDetail.telefono}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Dirección:</span>
                  <span class="text-sm font-medium text-gray-900">{clienteDetail.direccion}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Referido por:</span>
                  <span class="text-sm font-medium text-gray-900">{clienteDetail.referidoPor || '-'}</span>
                </div>
                <div class="flex justify-between items-center gap-2 flex-wrap">
                  <span class="text-sm text-gray-600">Ubicación GPS:</span>
                  {#if clienteDetail.lat != null && clienteDetail.lng != null}
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="text-sm font-medium text-gray-900">
                        {Number(clienteDetail.lat).toFixed(6)}, {Number(clienteDetail.lng).toFixed(6)}
                      </span>
                      <a
                        href="https://www.google.com/maps?q={clienteDetail.lat},{clienteDetail.lng}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100"
                      >
                        Ver en Google Maps
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                      </a>
                    </div>
                  {:else}
                    <span class="text-sm text-gray-500">No registradas</span>
                  {/if}
                </div>
              </div>
            </div>

            <!-- Suscripción -->
            <div>
              <h4 class="font-semibold text-gray-900 mb-3">Suscripción</h4>
              <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Plan:</span>
                  <Badge type={clienteDetail.plan === 'Premium' ? 'info' : 'default'}>
                    {clienteDetail.plan}
                  </Badge>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Estado:</span>
                  <Badge type={getBadgeType(clienteDetail.estado)}>
                    {clienteDetail.estado}
                  </Badge>
                </div>
              </div>
            </div>

            <!-- Visitas -->
            <div>
              <h4 class="font-semibold text-gray-900 mb-3">Programación</h4>
              <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Última visita:</span>
                  <span class="text-sm font-medium text-gray-900">
                    {clienteDetail.ultimaVisita ? new Date(clienteDetail.ultimaVisita).toLocaleDateString('es-AR') : 'Sin visitas'}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Próxima visita:</span>
                  <span class="text-sm font-medium text-primary-600">
                    {clienteDetail.proximaVisita ? new Date(clienteDetail.proximaVisita).toLocaleDateString('es-AR') : 'No programada'}
                  </span>
                </div>
              </div>
            </div>

            <!-- Acciones -->
            <div class="flex gap-2">
              <button class="flex-1 bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 font-medium">
                Ver reportes
              </button>
              <button class="flex-1 bg-white text-gray-700 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 font-medium">
                Editar cliente
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
{/if}
