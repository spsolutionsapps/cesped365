@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <div>
                        <h5 class="mb-0">Todos los Reportes del Jardín</h5>
                    </div>
                    <a href="{{ route('admin.garden-reports.create') }}" class="btn bg-gradient-primary btn-sm mb-0" type="button">+&nbsp; Nuevo Reporte</a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Usuario</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fecha</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Imágenes</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                            <tr>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $report->id }}</p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $report->user->name }}</p>
                                    <p class="text-xs text-secondary mb-0">{{ $report->user->email }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <p class="text-xs font-weight-bold mb-0">{{ \Carbon\Carbon::parse($report->report_date)->format('d/m/Y') }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <span class="badge badge-sm bg-{{ $report->general_status === 'good' ? 'success' : ($report->general_status === 'regular' ? 'warning' : 'danger') }}">
                                        {{ $report->general_status === 'good' ? 'Bueno' : ($report->general_status === 'regular' ? 'Regular' : 'Mejorar') }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ $report->images->count() }}</span>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('admin.garden-reports.show', $report) }}" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Ver">
                                        Ver
                                    </a>
                                    <a href="{{ route('admin.garden-reports.edit', $report) }}" class="text-secondary font-weight-bold text-xs ms-2" data-toggle="tooltip" data-original-title="Editar">
                                        Editar
                                    </a>
                                    <form action="{{ route('admin.garden-reports.destroy', $report) }}" method="POST" class="d-inline" id="delete-form-{{ $report->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="text-danger font-weight-bold text-xs border-0 bg-transparent ms-2 delete-btn" data-form-id="delete-form-{{ $report->id }}" data-item-name="Reporte #{{ $report->id }}">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-muted">No hay reportes registrados.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
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

