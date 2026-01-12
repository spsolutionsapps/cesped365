@extends('layouts.landing')

@section('title', 'CESPED 365 - Corte de pasto por suscripción mensual')

@section('content')
<!-- Hero Section -->
<section id="inicio" class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-4">CESPED 365</h1>
                <h2 class="display-5 mb-4">Corte de pasto por suscripción mensual</h2>
                <p class="lead mb-4">Un corte programado por mes. Precio fijo. Sin coordinar.</p>
                <p class="mb-4">Ideal para quienes quieren el jardín siempre prolijo sin ocuparse</p>
                <p class="mb-4"><strong>Tandil y alrededores.</strong></p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#planes" class="btn btn-hero-primary btn-lg">
                        <i class="fas fa-check-circle me-2"></i>Elegir mi categoría
                    </a>
                    <a href="https://wa.me/5491123456789" class="btn btn-hero-secondary btn-lg" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i>Hablar por WhatsApp
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800" alt="Jardín cuidado" class="img-fluid rounded shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section id="beneficios" class="benefits-section section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title text-center mb-5">BENEFICIOS EXCLUSIVOS DEL SUSCRIPTOR</h2>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="benefit-item">
                            <i class="fas fa-calendar-check"></i>
                            <strong>Visitas programadas y cumplimiento real</strong>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-cut"></i>
                            <strong>Un corte de césped mensual incluido.</strong>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-seedling"></i>
                            <strong>Jardín prolijo y parejo todo el año incluido bordes.</strong>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-camera"></i>
                            <strong>Monitoreo del jardín: seguimiento mensual, fotos y recomendaciones estacionales.</strong>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="benefit-item">
                            <i class="fas fa-credit-card"></i>
                            <strong>Pago automático, sin llamadas ni coordinaciones.</strong>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-lock"></i>
                            <strong>Precio mensual congelado todo el año</strong>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-percent"></i>
                            <strong>30% de descuento en cortes adicionales y servicios complementarios</strong>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-smile"></i>
                            <strong>Menos molestias y cero preocupaciones por el mantenimiento del césped</strong>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-times-circle"></i>
                            <strong>Podés cancelar la suscripción cuando quieras, sin contratos</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- What's Included Section -->
<section id="incluye" class="includes-section section">
    <div class="container">
        <h2 class="section-title text-center mb-4">QUE INCLUYE EL SERVICIO POR SUSCRIPCION</h2>
        <div class="includes-grid">
            <div class="include-card">
                <i class="fas fa-cut"></i>
                <h4>Un corte programado por mes + bordes + repaso de zonas de uso</h4>
            </div>
            <div class="include-card">
                <i class="fas fa-credit-card"></i>
                <h4>Pago automático</h4>
            </div>
            <div class="include-card">
                <i class="fas fa-lock"></i>
                <h4>Precio congelado</h4>
            </div>
            <div class="include-card">
                <i class="fas fa-percent"></i>
                <h4>30% de descuento en cortes extras solo para suscriptores</h4>
            </div>
            <div class="include-card">
                <i class="fas fa-camera"></i>
                <h4>Monitoreo del jardín: seguimiento mensual, fotos y recomendaciones estacionales</h4>
            </div>
            <div class="include-card">
                <i class="fas fa-calendar-check"></i>
                <h4>Visitas programadas y cumplimiento real</h4>
            </div>
        </div>
    </div>
</section>

<!-- Plans Section -->
<section id="planes" class="plans-section section">
    <div class="container">
        <h2 class="section-title text-center mb-4">Elegí la categoría de tu jardín</h2>
        <p class="text-center mb-5">Todos los planes con botón para pagar</p>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="plan-card">
                    <h3>Urbano</h3>
                    <p class="text-muted mb-3">Hasta 500 m²</p>
                    <div class="plan-price">$45.000</div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> Corte mensual incluido</li>
                        <li><i class="fas fa-check"></i> Bordes y repaso</li>
                        <li><i class="fas fa-check"></i> Monitoreo del jardín</li>
                        <li><i class="fas fa-check"></i> Pago automático</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-primary">Contratar Plan</a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="plan-card">
                    <h3>Residencial</h3>
                    <p class="text-muted mb-3">500 a 2.500 m²</p>
                    <div class="plan-price">$60.000</div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> Corte mensual incluido</li>
                        <li><i class="fas fa-check"></i> Bordes y repaso</li>
                        <li><i class="fas fa-check"></i> Monitoreo del jardín</li>
                        <li><i class="fas fa-check"></i> Pago automático</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-primary">Contratar Plan</a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="plan-card">
                    <h3>Parque / Quintas</h3>
                    <p class="text-muted mb-3">2.500 a 4.000 m²</p>
                    <div class="plan-price">$90.000</div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> Corte mensual incluido</li>
                        <li><i class="fas fa-check"></i> Bordes y repaso</li>
                        <li><i class="fas fa-check"></i> Monitoreo del jardín</li>
                        <li><i class="fas fa-check"></i> Pago automático</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-primary">Contratar Plan</a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="plan-card">
                    <h3>Especiales</h3>
                    <p class="text-muted mb-3">Campo de Deportes – Canchas de Polo – Limpieza de Terrenos - Áreas Públicas</p>
                    <div class="plan-price">Consultar</div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> Plan personalizado</li>
                        <li><i class="fas fa-check"></i> Precio según superficie</li>
                        <li><i class="fas fa-check"></i> Servicio adaptado</li>
                    </ul>
                    <a href="https://wa.me/5491123456789" class="btn btn-whatsapp" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i>Consultar
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-lg-12">
                <div class="important-note">
                    <strong>IMPORTANTE:</strong> No necesitas saber los metros exactos de tu jardín. Elegí la categoría que mejor lo represente. Si en la primera visita vemos que corresponde a otra categoría, lo ajustamos sin problema.
                </div>
                <p class="text-center mt-4">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Podés cancelar cuando quieras. Sin contratos. Sin permanencia.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Complementary Services Section -->
<section id="servicios" class="services-section section">
    <div class="container">
        <h2 class="section-title text-center mb-4">Servicios complementarios para tu jardín</h2>
        <p class="text-center mb-5">Además del corte mensual incluido, los suscriptores pueden acceder a servicios complementarios para el mantenimiento del jardín, con prioridad y un descuento del 30%.</p>
        
        <div class="services-grid">
            <div class="service-item">
                <i class="fas fa-leaf"></i>
                <h5>Recolección y retiro de pasto</h5>
            </div>
            <div class="service-item">
                <i class="fas fa-cut"></i>
                <h5>Cortes extras mensuales</h5>
            </div>
            <div class="service-item">
                <i class="fas fa-seedling"></i>
                <h5>Resiembra o siembra de césped</h5>
            </div>
            <div class="service-item">
                <i class="fas fa-tools"></i>
                <h5>Aireado del césped</h5>
            </div>
            <div class="service-item">
                <i class="fas fa-spray-can"></i>
                <h5>Fertilización de tu jardín</h5>
            </div>
            <div class="service-item">
                <i class="fas fa-broom"></i>
                <h5>Desmalezado de canteros</h5>
            </div>
            <div class="service-item">
                <i class="fas fa-leaf"></i>
                <h5>Limpieza de hojas</h5>
            </div>
            <div class="service-item">
                <i class="fas fa-tree"></i>
                <h5>Corte de arbustos-cercos</h5>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="https://wa.me/5491123456789" class="btn btn-whatsapp btn-lg" target="_blank">
                <i class="fab fa-whatsapp me-2"></i>Consultar
            </a>
        </div>
    </div>
</section>

<!-- Annual Plan Section -->
<section class="section bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800" alt="Plan anual" class="img-fluid rounded shadow section-image">
            </div>
            <div class="col-lg-6">
                <h2 class="section-title mb-4">Plan Anual</h2>
                <p class="lead mb-4">12 meses al precio de 10.</p>
                <p class="mb-4">Podés cancelar cuando quieras</p>
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-calendar-alt me-2"></i>Contratar Plan Anual
                </a>
            </div>
        </div>
                    </div>
</section>

<!-- How It Works Section -->
<section id="como-funciona" class="how-it-works-section section">
    <div class="container">
        <h2 class="section-title text-center mb-4">¿CÓMO FUNCIONA?</h2>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h4>Elegís la categoría</h4>
                    <p>Elegís la categoría y el plan que mejor se adapta a tu jardín.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h4>Elegís el pago</h4>
                    <p>Elegís pago mensual o anual.</p>
                </div>
                    </div>
            <div class="col-lg-4 mb-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h4>Listo</h4>
                    <p>Al instante te agendamos la primera visita y listo.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Zone Section -->
<section id="zona" class="zone-section section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="section-title mb-4">ZONA DE SERVICIO</h2>
                <p class="lead mb-4">Tandil y alrededores.</p>
                <p class="mb-4">Quiero saber si llegan a mi dirección</p>
                <a href="https://wa.me/5491123456789" class="btn btn-whatsapp btn-lg" target="_blank">
                    <i class="fab fa-whatsapp me-2"></i>Consultar
                </a>
                        </div>
            <div class="col-lg-6">
                <div class="map-container">
                    <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?w=800" alt="Mapa Tandil" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section id="preguntas" class="faq-section section">
    <div class="container">
        <h2 class="section-title text-center mb-5">PREGUNTAS Y RESPUESTAS</h2>
        <p class="text-center mb-5">Hace click aquí, para comprender mejor el servicio.</p>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                ¿Tengo que estar en mi casa cuando vienen?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                No, trabajamos de manera autónoma.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                ¿Puedo cancelar cuando quiera?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Sí, sin contratos.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                ¿Qué pasa si llueve?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Reprogramamos fecha.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                ¿En qué día del mes vienen?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Te asignamos una fecha fija según tu zona y plan.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                ¿Qué incluye exactamente la visita?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Corte, bordes, repaso en zonas de uso y un monitoreo del jardín.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                ¿Qué incluye el monitoreo?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Cada suscriptor tiene un acceso personal donde puede ver el estado y la evolución de su jardín cuando quiera de manera online.</p>
                                <p><strong>El panel incluye:</strong></p>
                                <ul>
                                    <li>Estado general del césped</li>
                                    <li>Indicador general del jardín (Bueno / Regular / A mejorar) con detalle sobre parejidad, color, manchas, zonas desgastadas y presencia de malezas, más una nota explicativa.</li>
                                    <li>Evolución del jardín en el tiempo - Comparación visual entre el mes actual y meses anteriores mediante imágenes cenitales (con drone).</li>
                                    <li>Nivel de crecimiento del mes - Medición aproximada del crecimiento del césped en centímetros, con categoría general (Bajo / Normal / Alto)</li>
                                    <li>Estado de compactación del suelo - Observación general del suelo (suelto o compacto) y recomendación si conviene aireado.</li>
                                    <li>Estado de humedad y riego - Evaluación visual del riego (seco, correcto o exceso de agua) con sugerencias simples de ajuste.</li>
                                    <li>Presencia de plagas o enfermedades visibles - Aviso si se detectan señales leves o situaciones a observar.</li>
                                    <li>Estado de canteros y bordes - Indicación de canteros prolijos o con malezas visibles y si requieren mantenimiento.</li>
                                    <li>Recomendaciones estacionales - Sugerencias prácticas sobre riego, corte, siembra o resiembra, fertilización y qué evitar ese mes.</li>
                                    <li>Historial de visitas - Registro de todas las visitas realizadas, con fecha y estado general en cada una.</li>
                                </ul>
                                <p><strong>Acceso al monitoreo:</strong> El panel está disponible las 24 horas y podés ingresar con tu usuario y clave, cuando quieras para consultar el estado actual de tu jardín o ver su evolución mes a mes.</p>
                                <p><strong>Aclaración:</strong> El monitoreo es informativo y forma parte de la suscripción. Los trabajos adicionales o intervenciones específicas se proponen como servicios complementarios y no están incluidos en el corte mensual.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                ¿Puedo pedir un corte extra o un tema puntual?
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Sí, con 30% de descuento para suscriptores.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                No sé cuántos m² tiene mi jardín, ¿qué hago?
                            </button>
                        </h2>
                        <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Escribinos y te ayudamos a definir cuál es el mejor plan.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                Mi casa tiene muy pocos m2, ¿no hay plan más chico?
                            </button>
                        </h2>
                        <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                El Plan Urbano está pensado para jardines chicos. Si tu caso es especial, escribinos y lo evaluamos.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                                ¿Cuáles son los extras que hacen en CESPED365?
                            </button>
                        </h2>
                        <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <ol>
                                    <li>Recolección y retiro de pasto (Embolsado y retiro del césped cortado)</li>
                                    <li>Cortes extras mensuales (Cortes adicionales fuera del incluido en la suscripción con descuento)</li>
                                    <li>Resiembra o siembra de césped (Reparación de zonas peladas o dañadas)</li>
                                    <li>Aireado del césped (Perforación del suelo para mejorar oxigenación y drenaje)</li>
                                    <li>Nivelación simple del terreno (Corrección de pequeños pozos o desniveles)</li>
                                    <li>Fertilización básica del césped (Aplicación de fertilizante estacional)</li>
                                    <li>Desmalezado de canteros (Eliminación de malezas en zonas plantadas)</li>
                                    <li>Reposición de tierra, corteza o chips</li>
                                    <li>Limpieza de hojas (Recolección de hojas caídas)</li>
                                    <li>Corte de arbustos bajos (Mantenimiento liviano sin poda grande)</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="nosotros" class="about-section section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800" alt="Equipo CESPED 365" class="img-fluid rounded shadow section-image">
            </div>
            <div class="col-lg-6">
                <h2 class="section-title mb-4">QUIÉNES SOMOS</h2>
                <p class="mb-3">Somos un equipo que se dedica al mantenimiento de jardines, pensamos este servicio para que tu casa tenga el jardín cuidado todo el año sin que tengas que estar pendiente.</p>
                <p class="mb-3">Armamos CÉSPED 365 porque muchas veces el jardín queda para después y termina descuidándose.</p>
                <p class="mb-3">Por eso trabajamos de forma distinta, con fechas claras y mantenimiento constante.</p>
                <p class="mb-3">Vamos una vez por mes, en una fecha acordada, hacemos el corte, los bordes y dejamos todo prolijo. Sin vueltas.</p>
                <p class="mb-3">Además, en cada visita observamos el estado general del jardín y su evolución con el tiempo, para poder acompañarlo mejor según la época del año.</p>
                <p class="mb-3">Contamos con las mejores máquinas que nos permiten trabajar rápido, tratando de molestar lo menos posible.</p>
                <p class="mb-4">La idea es simple: que no tengas que pensar en el jardín ni estar escribiéndole a nadie para que venga, solo disfrutar tu casa como corresponde.</p>
            </div>
        </div>
    </div>
</section>

<!-- Client Friend Program Section -->
<section class="client-friend-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2 class="section-title mb-4">PROGRAMA CLIENTE AMIGO</h2>
                <p class="lead mb-4">Recomendá CESPED365 a un amigo y obtené descuento de 2 cortes exclusivos en tu suscripción.</p>
                <a href="https://wa.me/5491123456789" class="btn btn-primary btn-lg" target="_blank">
                    <i class="fab fa-whatsapp me-2"></i>Más información
                </a>
            </div>
        </div>
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