@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <div>
                        <h5 class="mb-0">Todas las Suscripciones</h5>
                    </div>
                    <a href="{{ route('admin.subscriptions.create') }}" class="btn bg-gradient-primary btn-sm mb-0" type="button">+&nbsp; Nueva Suscripción</a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Usuario</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Plan</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Inicio</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fin</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $subscription)
                            <tr>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $subscription->id }}</p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $subscription->user->name }}</p>
                                    <p class="text-xs text-secondary mb-0">{{ $subscription->user->email }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <p class="text-xs font-weight-bold mb-0">{{ $subscription->plan->name }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <span class="badge badge-sm bg-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'suspended' ? 'warning' : 'danger') }}">
                                        {{ $subscription->status === 'active' ? 'Activa' : ($subscription->status === 'suspended' ? 'Suspendida' : ($subscription->status === 'expired' ? 'Expirada' : 'Cancelada')) }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ \Carbon\Carbon::parse($subscription->start_date)->format('d/m/Y') }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ \Carbon\Carbon::parse($subscription->end_date)->format('d/m/Y') }}</span>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Editar">
                                        Editar
                                    </a>
                                    <form action="{{ route('admin.subscriptions.destroy', $subscription) }}" method="POST" class="d-inline" id="delete-form-{{ $subscription->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="text-danger font-weight-bold text-xs border-0 bg-transparent delete-btn" data-form-id="delete-form-{{ $subscription->id }}" data-item-name="Suscripción #{{ $subscription->id }}">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted">No hay suscripciones registradas.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4">
                    {{ $subscriptions->links() }}
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
        buttons.forEach(function(button) {
            var newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var formId = this.getAttribute('data-form-id');
                var itemName = this.getAttribute('data-item-name');
                if (formId && itemName) {
                    showDeleteModal(formId, itemName);
                }
            });
        });
    }
    
    function attachConfirmHandler() {
        var confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (formToSubmit) {
                    formToSubmit.submit();
                }
            });
        }
    }
    
    function init() {
        attachDeleteHandlers();
        attachConfirmHandler();
    }
    
    function waitForBootstrap() {
        if (typeof bootstrap !== 'undefined') {
            init();
        } else {
            setTimeout(waitForBootstrap, 100);
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(waitForBootstrap, 500);
        });
    } else {
        setTimeout(waitForBootstrap, 500);
    }
    
    window.addEventListener('load', function() {
        setTimeout(waitForBootstrap, 1000);
    });
})();
</script>
@endpush
@endsection

