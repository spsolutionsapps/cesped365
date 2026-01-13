@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Detalles del Reporte</h5>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Usuario:</strong> {{ $gardenReport->user->name }}<br>
                            <strong>Email:</strong> {{ $gardenReport->user->email }}<br>
                            <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($gardenReport->report_date)->format('d/m/Y') }}<br>
                            <strong>Estado General:</strong>
                            <span class="badge bg-{{ $gardenReport->general_status === 'good' ? 'success' : ($gardenReport->general_status === 'regular' ? 'warning' : 'danger') }}">
                                {{ $gardenReport->general_status === 'good' ? 'Bueno' : ($gardenReport->general_status === 'regular' ? 'Regular' : 'Mejorar') }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Plan:</strong> {{ $gardenReport->subscription->plan->name }}<br>
                            <strong>Crecimiento:</strong> {{ $gardenReport->growth_cm }} cm ({{ $gardenReport->growth_category === 'low' ? 'Bajo' : ($gardenReport->growth_category === 'normal' ? 'Normal' : 'Alto') }})<br>
                            <strong>Suelo:</strong> {{ $gardenReport->soil_condition === 'loose' ? 'Suelto' : 'Compacto' }}<br>
                            <strong>Humedad:</strong> {{ $gardenReport->humidity_status === 'dry' ? 'Seco' : ($gardenReport->humidity_status === 'correct' ? 'Correcto' : 'Exceso') }}
                        </div>
                    </div>

                    @if($gardenReport->images->count() > 0)
                    <div class="mb-3">
                        <strong>Imágenes ({{ $gardenReport->images->count() }}):</strong>
                        <div class="image-gallery-container mt-2" style="display: flex; gap: 10px; flex-wrap: wrap;">
                            @foreach($gardenReport->images as $index => $image)
                            <div class="gallery-image-thumbnail" data-index="{{ $index }}" data-src="{{ asset('storage/' . $image->image_path) }}" style="width: 130px; height: 130px; border: 1px solid #ccc; border-radius: 8px; overflow: hidden; cursor: pointer;">
                                <img src="{{ asset('storage/' . $image->image_path) }}" alt="Imagen {{ $index + 1 }}" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($gardenReport->general_observations)
                    <div class="mb-3">
                        <strong>Observaciones Generales:</strong>
                        <p>{{ $gardenReport->general_observations }}</p>
                    </div>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.garden-reports.edit', $gardenReport) }}" class="btn bg-gradient-primary">Editar</a>
                        <a href="{{ route('admin.garden-reports.index') }}" class="btn bg-gradient-secondary">Volver</a>
                    </div>

                    <!-- Image Gallery Modal -->
                    <div class="modal fade" id="imageGalleryModal" tabindex="-1" aria-labelledby="imageGalleryModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-0">
                                    <!-- Close button inside image area -->
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>

                                    <!-- Main Image Display -->
                                    <div class="gallery-main-image">
                                        <img id="galleryMainImage" src="" alt="Imagen ampliada">

                                        <!-- Navigation Arrows over image -->
                                        <button class="gallery-nav gallery-nav-prev" id="galleryPrevBtn">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button class="gallery-nav gallery-nav-next" id="galleryNextBtn">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>

                                    <!-- Thumbnails centered below -->
                                    <div class="gallery-thumbnails">
                                        @foreach($gardenReport->images as $index => $image)
                                        <div class="gallery-thumbnail {{ $index === 0 ? 'active' : '' }}"
                                             data-image-index="{{ $index }}"
                                             data-image-src="{{ asset('storage/' . $image->image_path) }}">
                                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="Miniatura {{ $index + 1 }}">
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Gallery script loaded');

    const imageGalleryModal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
    const mainImage = document.getElementById('galleryMainImage');
    const prevBtn = document.getElementById('galleryPrevBtn');
    const nextBtn = document.getElementById('galleryNextBtn');

    let currentIndex = 0;
    let images = [];

    // Initialize images array from thumbnails
    const thumbnails = document.querySelectorAll('.gallery-image-thumbnail');
    thumbnails.forEach((thumbnail, index) => {
        const img = thumbnail.querySelector('img');
        if (img) {
            images.push({
                src: thumbnail.dataset.src,
                index: parseInt(thumbnail.dataset.index),
                alt: img.alt
            });

            // Add click event to thumbnail
            thumbnail.addEventListener('click', function() {
                console.log('Thumbnail clicked:', thumbnail.dataset.index);
                openGallery(parseInt(thumbnail.dataset.index));
            });
        }
    });

    console.log('Images found:', images.length);

    function openGallery(index) {
        console.log('Opening gallery at index:', index);
        currentIndex = index;
        mainImage.src = images[currentIndex].src;
        mainImage.alt = images[currentIndex].alt;
        updateThumbnails();
        imageGalleryModal.show();
    }

    function updateThumbnails() {
        document.querySelectorAll('.gallery-thumbnail').forEach((thumb, index) => {
            thumb.classList.toggle('active', index === currentIndex);
        });
    }

    function showNext() {
        currentIndex = (currentIndex + 1) % images.length;
        mainImage.src = images[currentIndex].src;
        mainImage.alt = images[currentIndex].alt;
        updateThumbnails();
    }

    function showPrev() {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        mainImage.src = images[currentIndex].src;
        mainImage.alt = images[currentIndex].alt;
        updateThumbnails();
    }

    // Event listeners
    if (nextBtn) nextBtn.addEventListener('click', showNext);
    if (prevBtn) prevBtn.addEventListener('click', showPrev);

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (!document.getElementById('imageGalleryModal').classList.contains('show')) return;

        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            showNext();
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            showPrev();
        } else if (e.key === 'Escape') {
            imageGalleryModal.hide();
        }
    });

    // Thumbnail click events in modal
    document.querySelectorAll('.gallery-thumbnail').forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', function() {
            currentIndex = index;
            mainImage.src = images[currentIndex].src;
            mainImage.alt = images[currentIndex].alt;
            updateThumbnails();
        });
    });
});
</script>
@endpush

@endsection

