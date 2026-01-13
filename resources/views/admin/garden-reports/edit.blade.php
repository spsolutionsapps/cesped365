@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Editar Reporte del Jardín</h5>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <form action="{{ route('admin.garden-reports.update.post', $gardenReport) }}" method="POST" enctype="multipart/form-data" class="p-4">
                    @csrf
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Usuario</label>
                        <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $gardenReport->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Suscripción actual: <strong>{{ $gardenReport->subscription->plan->name }}</strong> 
                            ({{ $gardenReport->subscription->status === 'active' ? 'Activa' : ($gardenReport->subscription->status === 'suspended' ? 'Suspendida' : ($gardenReport->subscription->status === 'expired' ? 'Expirada' : 'Cancelada')) }}). 
                            Si cambia el usuario, se utilizará automáticamente su suscripción activa.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="report_date" class="form-label">Fecha del Reporte</label>
                        <input type="date" class="form-control @error('report_date') is-invalid @enderror" id="report_date" name="report_date" value="{{ old('report_date', $gardenReport->report_date->format('Y-m-d')) }}" required>
                        @error('report_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="general_status" class="form-label">Estado General</label>
                        <select class="form-control @error('general_status') is-invalid @enderror" id="general_status" name="general_status" required>
                            <option value="good" {{ old('general_status', $gardenReport->general_status) === 'good' ? 'selected' : '' }}>Bueno</option>
                            <option value="regular" {{ old('general_status', $gardenReport->general_status) === 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="improve" {{ old('general_status', $gardenReport->general_status) === 'improve' ? 'selected' : '' }}>Mejorar</option>
                        </select>
                        @error('general_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($gardenReport->images->count() > 0)
                    <div class="mb-4">
                        <label class="form-label">Imágenes Existentes ({{ $gardenReport->images->count() }})</label>
                        <p class="text-sm text-muted mb-3">Estas imágenes ya están guardadas. Puedes agregar más imágenes abajo.</p>
                        <div class="existing-images-container" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
                            @foreach($gardenReport->images as $index => $image)
                            <div class="existing-image-card" style="position: relative; width: 120px; height: 120px; border: 2px solid #e9ecef; border-radius: 8px; overflow: hidden;">
                                <img src="{{ $image->public_url }}" alt="Imagen existente {{ $index + 1 }}" style="width: 100%; height: 100%; object-fit: cover;">
                                <button type="button" class="delete-image-btn btn btn-sm btn-danger rounded-circle position-absolute"
                                        data-image-id="{{ $image->id }}"
                                        data-image-path="{{ $image->image_path }}"
                                        style="top: 8px; right: 8px; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; z-index: 10;"
                                        title="Eliminar imagen">
                                    <i class="fas fa-trash" style="font-size: 12px;"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="images-edit" class="form-label">Agregar Imágenes</label>
                        <p class="text-sm text-muted mb-2">Sube hasta 6 imágenes adicionales. La primera será la imagen principal.</p>
                        <div class="image-uploader" id="image-uploader-edit">
                            <div class="image-dropzone" id="image-dropzone-edit">
                                <div class="add-card text-center">
                                    <i class="fas fa-plus fa-2x mb-2 text-secondary"></i>
                                    <p class="mb-0 fw-bold">Agregar</p>
                                </div>
                                <input type="file" class="d-none @error('images') is-invalid @enderror" id="images-edit" name="images[]" accept="image/*" multiple>
                            </div>
                            <div class="image-previews-container" id="image-previews-edit"></div>
                            <p class="text-sm text-muted mt-2" id="image-counter-edit">0 de 6 imágenes</p>
                            <p class="text-xs text-muted mb-0">Formatos JPG o PNG, máximo 2 MB por imagen.</p>
                        </div>
                        @error('images')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn bg-gradient-primary">Actualizar Reporte</button>
                        <a href="{{ route('admin.garden-reports.index') }}" class="btn bg-gradient-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación de Imagen -->
<div class="modal fade" id="confirmDeleteImageModal" tabindex="-1" aria-labelledby="confirmDeleteImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg bg-white" style="background-color: #fff;">
            <div class="modal-header border-0 bg-gradient-danger text-white">
                <h5 class="modal-title d-flex align-items-center" id="confirmDeleteImageModalLabel">
                    <i class="ni ni-notification-70 me-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4 px-4 bg-white">
                <div class="mb-3">
                    <i class="ni ni-notification-70 text-danger" style="font-size: 3.5rem;"></i>
                </div>
                <p class="mb-2 fs-6 fw-bold">¿Estás seguro?</p>
                <p class="mb-0 fs-6 text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4 bg-white" style="background-color: #fff;">
                <button type="button" class="btn bg-gradient-secondary text-white px-4 me-2" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-gradient-danger text-white px-4" id="confirmDeleteImageBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportId = {{ $gardenReport->id }};
    const modalElement = document.getElementById('confirmDeleteImageModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteImageBtn');
    const modalInstance = (typeof bootstrap !== 'undefined' && modalElement)
        ? new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false })
        : null;

    let pendingDelete = null; // { imageId: string, cardEl: Element }


    // Handle delete image buttons
    const deleteButtons = document.querySelectorAll('.delete-image-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const imageId = this.getAttribute('data-image-id');
            const cardEl = this.closest('.existing-image-card');

            pendingDelete = { imageId, cardEl };

            if (modalInstance) {
                modalInstance.show();
            } else {
                // Fallback (por si bootstrap no está disponible)
                if (confirm('¿Estás seguro de que quieres eliminar esta imagen? Esta acción no se puede deshacer.')) {
                    doDeletePending();
                }
            }
        });

        // Add hover effects for the delete button
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.3)';
        });

        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
        });
    });

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            doDeletePending();
        });
    }

    function doDeletePending() {
        if (!pendingDelete?.imageId) return;

        const imageId = pendingDelete.imageId;
        const cardEl = pendingDelete.cardEl;

        // Make AJAX call to delete the image
        fetch(`/admin/garden-reports/${reportId}/images/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(async (response) => {
            const contentType = response.headers.get('content-type') || '';
            if (!response.ok) {
                const text = await response.text();
                throw new Error(`HTTP ${response.status}: ${text}`);
            }
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Unexpected response: ${text}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Hide the image card
                if (cardEl) cardEl.style.display = 'none';
                // Update counter if needed
                updateImageCounter();
                // Show success toast using the professional notification system
                NotificationSystem.success(data.message || 'Imagen eliminada exitosamente.', 3000);
                pendingDelete = null;
                if (modalInstance) modalInstance.hide();
            } else {
                NotificationSystem.error('Error al eliminar la imagen: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            NotificationSystem.error('Error al eliminar la imagen. Inténtalo de nuevo.');
        });
    }

    function updateImageCounter() {
        const visibleCards = document.querySelectorAll('.existing-image-card[style*="display: none"]').length;
        const totalCards = document.querySelectorAll('.existing-image-card').length;
        const actualVisible = totalCards - document.querySelectorAll('.existing-image-card[style*="display: none"]').length;
        // You can update a counter display here if needed
    }
});

    (function() {
        function initImageUploader(dropId, inputId, previewsId, counterId) {
            const dropzone = document.getElementById(dropId);
            const input = document.getElementById(inputId);
            const previews = document.getElementById(previewsId);
            const counter = document.getElementById(counterId);
            if (!dropzone || !input || !previews || !counter) return;

            const dataTransfer = new DataTransfer();
            const maxImages = 6;

            function renderPreviews() {
                previews.innerHTML = '';
                Array.from(dataTransfer.files).forEach((file, index) => {
                    const card = document.createElement('div');
                    card.className = 'image-preview-card';

                    const img = document.createElement('img');
                    img.className = 'image-preview';
                    img.src = URL.createObjectURL(file);
                    img.alt = file.name;

                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'image-preview-remove';
                    removeBtn.innerHTML = '×';
                    removeBtn.onclick = function() {
                        const files = Array.from(dataTransfer.files);
                        files.splice(index, 1);
                        dataTransfer.items.clear();
                        files.forEach(f => dataTransfer.items.add(f));
                        input.files = dataTransfer.files;
                        renderPreviews();
                    };

                    card.appendChild(img);
                    card.appendChild(removeBtn);
                    previews.appendChild(card);
                });
                counter.textContent = dataTransfer.files.length + ' de ' + maxImages + ' imágenes';
            }

            function addFiles(files) {
                Array.from(files).forEach(file => {
                    if (dataTransfer.files.length >= maxImages) return;
                    if (!file.type.startsWith('image/')) return;
                    if (file.size > 2 * 1024 * 1024) return;
                    dataTransfer.items.add(file);
                });
                input.files = dataTransfer.files;
                renderPreviews();
            }

            dropzone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropzone.classList.add('drag-over');
            });

            dropzone.addEventListener('dragleave', function() {
                dropzone.classList.remove('drag-over');
            });

            dropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropzone.classList.remove('drag-over');
                addFiles(e.dataTransfer.files);
            });

            dropzone.addEventListener('click', function() {
                input.click();
            });

            input.addEventListener('change', function(e) {
                addFiles(e.target.files);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initImageUploader('image-dropzone-edit', 'images-edit', 'image-previews-edit', 'image-counter-edit');
        });
    })();
</script>
@endpush
@endsection

