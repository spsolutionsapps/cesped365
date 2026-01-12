@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <div>
                        <h5 class="mb-0">Todos los Usuarios</h5>
                    </div>
                    <a href="{{ route('admin.users.create') }}" class="btn bg-gradient-primary btn-sm mb-0" type="button">+&nbsp; Nuevo Usuario</a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nombre</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Email</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Rol</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Suscripciones</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pagos</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fecha Creación</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $user->id }}</p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $user->name }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <p class="text-xs font-weight-bold mb-0">{{ $user->email }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <span class="badge badge-sm bg-{{ $user->role === 'admin' ? 'danger' : 'success' }}">
                                        {{ $user->role === 'admin' ? 'Administrador' : 'Cliente' }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    @if($user->subscriptions->count() > 0)
                                        <span class="badge badge-sm bg-info">{{ $user->subscriptions->count() }}</span>
                                        <br>
                                        <small class="text-xs text-muted">
                                            @php
                                                $activeSubs = $user->subscriptions->where('status', 'active')->count();
                                            @endphp
                                            {{ $activeSubs }} activa(s)
                                        </small>
                                    @else
                                        <span class="text-muted text-xs">Sin suscripciones</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    @if($user->payments->count() > 0)
                                        <span class="badge badge-sm bg-success">{{ $user->payments->count() }}</span>
                                        <br>
                                        <small class="text-xs text-muted">
                                            @php
                                                $totalPaid = $user->payments->where('status', 'approved')->sum('amount');
                                                $pending = $user->payments->where('status', 'pending')->count();
                                            @endphp
                                            ${{ number_format($totalPaid, 2, ',', '.') }}
                                            @if($pending > 0)
                                                <br><span class="text-warning">{{ $pending }} pendiente(s)</span>
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted text-xs">Sin pagos</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ $user->created_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Editar">
                                        Editar
                                    </a>
                                    @if($user->role !== 'admin')
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" id="delete-form-{{ $user->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="text-danger font-weight-bold text-xs border-0 bg-transparent delete-btn" data-form-id="delete-form-{{ $user->id }}" data-item-name="{{ $user->name }}">Eliminar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted">No hay usuarios registrados.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 bg-gradient-danger text-white">
                <h5 class="modal-title d-flex align-items-center" id="confirmDeleteModalLabel">
                    <i class="ni ni-notification-70 me-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4 px-4">
                <div class="mb-3">
                    <i class="ni ni-notification-70 text-danger" style="font-size: 3.5rem;"></i>
                </div>
                <p class="mb-2 fs-6 fw-bold">¿Estás seguro?</p>
                <p class="mb-0 fs-6 text-muted" id="delete-item-name">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn bg-gradient-secondary text-white px-4 me-2" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-danger text-white px-4" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var formToSubmit = null;
    var confirmDeleteModalInstance = null;
    
    function showDeleteModal(formId, itemName) {
        var modalElement = document.getElementById('confirmDeleteModal');
        var deleteItemName = document.getElementById('delete-item-name');
        
        if (!modalElement) {
            alert('Error: Modal no encontrado');
            return;
        }
        
        formToSubmit = document.getElementById(formId);
        if (!formToSubmit) {
            alert('Error: Formulario no encontrado');
            return;
        }
        
        if (deleteItemName && itemName) {
            deleteItemName.textContent = '¿Deseas eliminar "' + itemName + '"? Esta acción no se puede deshacer.';
        }
        
        // Usar Bootstrap si está disponible
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            if (!confirmDeleteModalInstance) {
                confirmDeleteModalInstance = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
            }
            confirmDeleteModalInstance.show();
        } else {
            // Fallback manual
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            modalElement.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            var backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'delete-modal-backdrop';
            document.body.appendChild(backdrop);
        }
    }
    
    function attachDeleteHandlers() {
        var buttons = document.querySelectorAll('.delete-btn');
        console.log('Encontrados ' + buttons.length + ' botones de eliminar');
        
        buttons.forEach(function(button) {
            // Remover cualquier listener anterior
            var newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Botón de eliminar clickeado');
                
                var formId = this.getAttribute('data-form-id');
                var itemName = this.getAttribute('data-item-name');
                
                console.log('Form ID:', formId, 'Item Name:', itemName);
                
                if (formId && itemName) {
                    showDeleteModal(formId, itemName);
                } else {
                    alert('Error: Datos faltantes');
                }
            });
        });
    }
    
    function attachConfirmHandler() {
        var confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                console.log('Confirmar eliminación clickeado');
                if (formToSubmit) {
                    console.log('Enviando formulario:', formToSubmit.id);
                    formToSubmit.submit();
                } else {
                    alert('Error: Formulario no encontrado');
                }
            });
        }
    }
    
    function init() {
        console.log('Inicializando modales de eliminación...');
        attachDeleteHandlers();
        attachConfirmHandler();
    }
    
    // Esperar a que Bootstrap esté cargado
    function waitForBootstrap() {
        if (typeof bootstrap !== 'undefined') {
            console.log('Bootstrap disponible');
            init();
        } else {
            console.log('Esperando Bootstrap...');
            setTimeout(waitForBootstrap, 100);
        }
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(waitForBootstrap, 500);
        });
    } else {
        setTimeout(waitForBootstrap, 500);
    }
    
    // También intentar después de que todo esté cargado
    window.addEventListener('load', function() {
        setTimeout(waitForBootstrap, 1000);
    });
})();
</script>
@endpush
@endsection

