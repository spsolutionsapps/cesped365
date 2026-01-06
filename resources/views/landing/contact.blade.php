@extends('layouts.landing')

@section('title', 'Contacto - Césped365')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4">Contáctanos</h1>
        <p class="lead">Estamos aquí para ayudarte</p>
    </div>
</section>

<!-- Contact Section -->
<section class="section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <h2 class="mb-4">Información de Contacto</h2>
                <div class="mb-4">
                    <h5><i class="fas fa-envelope text-primary me-2"></i> Email</h5>
                    <p>info@cesped365.com</p>
                </div>
                <div class="mb-4">
                    <h5><i class="fas fa-phone text-primary me-2"></i> Teléfono</h5>
                    <p>+54 11 1234-5678</p>
                </div>
                <div class="mb-4">
                    <h5><i class="fas fa-map-marker-alt text-primary me-2"></i> Dirección</h5>
                    <p>Buenos Aires, Argentina</p>
                </div>
                <div class="mb-4">
                    <h5><i class="fas fa-clock text-primary me-2"></i> Horarios</h5>
                    <p>Lunes a Viernes: 9:00 - 18:00<br>Sábados: 9:00 - 13:00</p>
                </div>
            </div>
            <div class="col-lg-6">
                <h2 class="mb-4">Envíanos un Mensaje</h2>
                <form>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Asunto</label>
                        <input type="text" class="form-control" id="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Mensaje</label>
                        <textarea class="form-control" id="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

