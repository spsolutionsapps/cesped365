@extends('layouts.landing')

@section('title', 'Planes - Césped365')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4">Elige el plan perfecto para ti</h1>
        <p class="lead">Planes flexibles adaptados a tus necesidades</p>
    </div>
</section>

<!-- Plans Section -->
<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            @forelse($plans as $plan)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="plan-card card h-100 {{ $plan->type === 'yearly' ? 'featured' : '' }}">
                    <div class="card-body text-center p-4">
                        <h3 class="card-title mb-3">{{ $plan->name }}</h3>
                        <div class="mb-4">
                            <span class="display-4 fw-bold">${{ number_format($plan->price, 0, ',', '.') }}</span>
                            <span class="text-muted">/{{ $plan->type === 'monthly' ? 'mes' : 'año' }}</span>
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Visitas regulares programadas</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Reportes detallados del jardín</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Fotos de cada visita</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Recomendaciones profesionales</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Duración: {{ $plan->duration_months }} meses</li>
                        </ul>
                        @auth
                            <a href="{{ route('dashboard.subscription') }}" class="btn btn-primary w-100">Gestionar Suscripción</a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-primary w-100">Contratar Plan</a>
                        @endauth
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center">
                <p class="lead">No hay planes disponibles en este momento.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Preguntas Frecuentes</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                ¿Cómo funciona el servicio?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Una vez que te suscribas a un plan, programaremos visitas regulares según la frecuencia de tu plan. En cada visita, nuestro equipo cortará tu césped y creará un reporte detallado con fotos y recomendaciones.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                ¿Puedo cancelar mi suscripción?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Sí, puedes cancelar tu suscripción en cualquier momento desde tu panel de control. No hay penalizaciones por cancelación.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                ¿Qué métodos de pago aceptan?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Aceptamos pagos a través de Mercado Pago con tarjetas de crédito, débito y otros métodos disponibles en la plataforma.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

