@extends('layouts.landing')

@section('title', 'Césped365 - Servicio de Corte de Césped por Suscripción')

@section('content')
<!-- Hero Section -->
<section id="inicio" class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Tu jardín perfecto, sin esfuerzo</h1>
                <p class="lead mb-4">Servicio profesional de corte de césped por suscripción. Nos encargamos de mantener tu jardín impecable todo el año.</p>
                <div class="d-flex gap-3">
                    <a href="#planes" class="btn btn-light btn-lg">Ver Planes</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Comenzar Ahora</a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="fas fa-seedling" style="font-size: 200px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="caracteristicas" class="section bg-light">
    <div class="container">
        <h2 class="text-center mb-5">¿Por qué elegirnos?</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <h4>Visitas Programadas</h4>
                    <p>Visitas regulares según tu plan. Nunca más te preocupes por el mantenimiento de tu césped.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-clipboard-check fa-2x"></i>
                    </div>
                    <h4>Reportes Detallados</h4>
                    <p>Recibe reportes completos del estado de tu jardín con fotos y recomendaciones profesionales.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-tools fa-2x"></i>
                    </div>
                    <h4>Equipos Profesionales</h4>
                    <p>Utilizamos equipos de última generación para garantizar el mejor resultado en cada visita.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Plans Section -->
<section id="planes" class="section">
    <div class="container">
        <h2 class="text-center mb-5">Elige el plan perfecto para ti</h2>
        <p class="text-center lead mb-5">Planes flexibles adaptados a tus necesidades</p>
        <div class="row justify-content-center">
            @php
                $plans = \App\Models\Plan::where('active', true)->get();
            @endphp
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
<section id="preguntas" class="section bg-light">
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

<!-- Contact Section -->
<section id="contacto" class="section">
    <div class="container">
        <h2 class="text-center mb-5">Contáctanos</h2>
        <p class="text-center lead mb-5">Estamos aquí para ayudarte</p>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <h3 class="mb-4">Información de Contacto</h3>
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
                <h3 class="mb-4">Envíanos un Mensaje</h3>
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

<!-- CTA Section -->
<section class="section bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">¿Listo para tener el jardín de tus sueños?</h2>
        <p class="lead mb-4">Únete a cientos de clientes satisfechos y disfruta de un césped perfecto todo el año.</p>
        <a href="{{ route('register') }}" class="btn btn-light btn-lg">Comenzar Ahora</a>
    </div>
</section>
@endsection

@push('styles')
<style>
    html {
        scroll-behavior: smooth;
    }
    
    section {
        scroll-margin-top: 80px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Smooth scroll para enlaces del navbar
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Actualizar navbar activo al hacer scroll
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('section[id]');
        const scrollPos = window.scrollY + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + sectionId) {
                        link.classList.add('active');
                    }
                });
            }
        });
    });
</script>
@endpush
