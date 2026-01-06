<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Césped365 - Servicio de Corte de Césped por Suscripción')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Landing Page CSS -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-landing fixed-top" id="mainNavbar">
        <div class="navbar-content-wrapper">
            <a class="navbar-brand fw-bold" href="#inicio">
                <i class="fas fa-seedling"></i> CESPED 365
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#inicio">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#beneficios">Beneficios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#planes">Planes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#servicios">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#preguntas">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#nosotros">Nosotros</a>
                    </li>
                </ul>
                <div class="navbar-actions">
                    @auth
                        <a class="nav-link d-inline-block" href="{{ route('dashboard') }}">Dashboard</a>
                    @else
                        <a class="nav-link d-inline-block me-3" href="{{ route('login') }}">Iniciar Sesión</a>
                    @endauth
                    <a class="btn btn-primary btn-contact" href="https://wa.me/5491123456789" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i>Contactar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-landing">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-seedling"></i> CESPED 365</h5>
                    <p>Servicio de corte de pasto por suscripción mensual<br>Zona Tandil y alrededores.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#preguntas">Preguntas frecuentes</a></li>
                        <li><a href="#planes">Cancelar suscripción</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>¿Sos jardinero?</h5>
                    <p>Sumate al equipo CESPED 365</p>
                    <a href="https://wa.me/5491123456789" class="btn btn-whatsapp" target="_blank">
                        <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
                    </a>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="mb-0">&copy; {{ date('Y') }} CESPED 365. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Hide/show navbar on scroll
        (function() {
            let lastScrollTop = 0;
            const navbar = document.getElementById('mainNavbar');
            let ticking = false;
            
            function updateNavbar() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > 100) {
                    if (scrollTop > lastScrollTop && scrollTop - lastScrollTop > 5) {
                        // Scrolling down - hide navbar
                        navbar.classList.add('navbar-hidden');
                    } else if (scrollTop < lastScrollTop && lastScrollTop - scrollTop > 5) {
                        // Scrolling up - show navbar
                        navbar.classList.remove('navbar-hidden');
                    }
                } else {
                    // At top of page - always show
                    navbar.classList.remove('navbar-hidden');
                }
                
                lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
                ticking = false;
            }
            
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(updateNavbar);
                    ticking = true;
                }
            });
        })();
        
        // Cerrar navbar móvil al hacer clic en un enlace
        document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                    bsCollapse.hide();
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>

