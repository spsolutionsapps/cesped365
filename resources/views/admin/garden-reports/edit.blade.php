@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Editar Reporte del Jardín</h5>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <form action="{{ route('admin.garden-reports.update', $gardenReport) }}" method="POST" enctype="multipart/form-data" class="p-4">
                    @csrf
                    @method('PUT')
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
                                <img src="{{ asset('storage/' . $image->image_path) }}" alt="Imagen existente {{ $index + 1 }}" style="width: 100%; height: 100%; object-fit: cover;">
                                <div class="image-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;">
                                    <button type="button" class="btn btn-danger btn-sm delete-image-btn"
                                            data-image-id="{{ $image->id }}"
                                            data-image-path="{{ $image->image_path }}"
                                            style="font-size: 12px;">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
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


@push('scripts')
<script>
    // Handle existing image hover effects
    document.addEventListener('DOMContentLoaded', function() {
        const imageCards = document.querySelectorAll('.existing-image-card');

        imageCards.forEach(card => {
            const overlay = card.querySelector('.image-overlay');

            card.addEventListener('mouseenter', function() {
                overlay.style.opacity = '1';
            });

            card.addEventListener('mouseleave', function() {
                overlay.style.opacity = '0';
            });
        });

        // Handle delete image buttons
        const deleteButtons = document.querySelectorAll('.delete-image-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const imageId = this.getAttribute('data-image-id');
                const imagePath = this.getAttribute('data-image-path');

                if (confirm('¿Estás seguro de que quieres eliminar esta imagen?')) {
                    // For now, just hide the image. In production, you'd make an AJAX call to delete it
                    this.closest('.existing-image-card').style.display = 'none';

                    // You could add an AJAX call here to actually delete the image
                    // fetch(`/admin/garden-reports/images/${imageId}`, {
                    //     method: 'DELETE',
                    //     headers: {
                    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    //     }
                    // });
                }
            });
        });
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

