@extends('layouts.landing')

@section('title', 'CESPED 365 - Corte de pasto por suscripción mensual')

@section('content')
<!-- Coming Soon Section -->
<section class="hero-section min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="display-1 fw-bold mb-4" style="font-size: 4rem; color: #2d3748;">CESPED 365</h1>
                <h2 class="display-4 mb-5" style="color: #4a5568;">Corte de pasto por suscripción mensual</h2>

                <div class="mt-5">
                    <a href="https://wa.me/5491123456789" class="btn btn-success btn-lg px-5 py-3" target="_blank" style="font-size: 1.2rem;">
                        <i class="fab fa-whatsapp me-3" style="font-size: 1.5rem;"></i>
                        Contactar por WhatsApp
                    </a>
                </div>

                <p class="mt-4 text-muted">Próximamente más información sobre nuestros servicios</p>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-success {
    background-color: #25d366 !important;
    border-color: #25d366 !important;
}

.btn-success:hover {
    background-color: #128c7e !important;
    border-color: #128c7e !important;
}
</style>
@endsection