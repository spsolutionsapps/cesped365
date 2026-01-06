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
    
    <style>
        :root {
            --primary-color: #17a2b8;
            --secondary-color: #6c757d;
            --success-color: #28a745;
        }
        
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: none;
            transition: background-color 0.3s;
        }
        
        .navbar.scrolled {
            background-color: rgba(255, 255, 255, 0.98);
        }
        
        .navbar-nav .nav-link.active {
            color: var(--primary-color) !important;
            font-weight: bold;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        
        .section {
            padding: 80px 0;
        }
        
        .plan-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .plan-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: none;
        }
        
        .plan-card.featured {
            border-color: var(--primary-color);
            position: relative;
        }
        
        .plan-card.featured::before {
            content: 'Popular';
            position: absolute;
            top: -15px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        footer {
            background-color: #343a40;
            color: white;
            padding: 40px 0 20px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#inicio">
                <i class="fas fa-seedling text-success"></i> Césped365
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#inicio">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#caracteristicas">Características</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#planes">Planes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#preguntas">Preguntas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contacto">Contacto</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="{{ route('register') }}">Registrarse</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main style="margin-top: 76px;">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-seedling text-success"></i> Césped365</h5>
                    <p>Servicio profesional de corte de césped por suscripción. Mantén tu jardín perfecto todo el año.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Enlaces</h5>
                    <ul class="list-unstyled">
                        <li><a href="#inicio" class="text-white-50">Inicio</a></li>
                        <li><a href="#caracteristicas" class="text-white-50">Características</a></li>
                        <li><a href="#planes" class="text-white-50">Planes</a></li>
                        <li><a href="#preguntas" class="text-white-50">Preguntas</a></li>
                        <li><a href="#contacto" class="text-white-50">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contacto</h5>
                    <p class="text-white-50">
                        <i class="fas fa-envelope"></i> info@cesped365.com<br>
                        <i class="fas fa-phone"></i> +54 11 1234-5678
                    </p>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p class="mb-0 text-white-50">&copy; {{ date('Y') }} Césped365. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Cambiar navbar al hacer scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Cerrar navbar móvil al hacer clic en un enlace
        document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                    bsCollapse.hide();
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>

