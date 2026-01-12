<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CESPED 365 - Próximamente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Coming Soon Section -->
    <section class="coming-soon-section min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-1 fw-bold mb-4" style="font-size: 5rem; color: white; text-shadow: 3px 3px 6px rgba(0,0,0,0.7);">
                        CESPED 365
                    </h1>
                    <h2 class="display-4 mb-5" style="color: white; font-weight: 300; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                        Corte de pasto por suscripción mensual
                    </h2>

                    <div class="mt-5">
                        <a href="https://wa.me/5491123456789" class="btn btn-whatsapp btn-lg px-5 py-3" target="_blank" style="font-size: 1.3rem;">
                            <i class="fab fa-whatsapp me-3" style="font-size: 1.5rem;"></i>
                            Contactar por WhatsApp
                        </a>
                    </div>

                    <p class="mt-4 text-white lead" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">Próximamente más información sobre nuestros servicios</p>
                </div>
            </div>
        </div>
    </section>

    <style>
    body {
        margin: 0;
        padding: 0;
    }

    .coming-soon-section {
        background: url('https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1920') no-repeat center center fixed;
        background-size: cover;
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
    }

    .coming-soon-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1;
    }

    .coming-soon-section .container {
        position: relative;
        z-index: 2;
    }

    .btn-whatsapp {
        background-color: #25d366 !important;
        border-color: #25d366 !important;
        color: white !important;
        transition: all 0.3s ease;
        border-radius: 50px;
        font-weight: bold;
    }

    .btn-whatsapp:hover {
        background-color: #128c7e !important;
        border-color: #128c7e !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
        color: white !important;
    }

    .display-1 {
        font-family: 'Arial Black', Arial, sans-serif;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .display-4 {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        letter-spacing: 1px;
    }

    /* Ocultar cualquier header/footer que pueda venir del layout */
    header, footer, nav, .navbar {
        display: none !important;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>