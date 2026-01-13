<!--
=========================================================
* Soft UI Dashboard - v1.0.3
=========================================================

* Product Page: https://www.creative-tim.com/product/soft-ui-dashboard
* Copyright 2021 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)

* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<!DOCTYPE html>

<html lang="es" >

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  @if (env('IS_DEMO'))
      <x-demo-metas></x-demo-metas>
  @endif

  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
  <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
  <title>
    Césped 365 - Dashboard
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- CSS Files -->
  <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css?v=1.0.3') }}" rel="stylesheet" />
  <!-- Custom CSS - Remove all shadows -->
  <link href="{{ asset('assets/css/custom-no-shadows.css') }}" rel="stylesheet" />
  <!-- Garden Reports Styles -->
  <link href="{{ asset('css/garden-reports.css') }}" rel="stylesheet" />
  <!-- Notification System Styles -->
  <style>
    .notification-container {
      z-index: 10600 !important;
      pointer-events: none !important;
    }

    .fallback-toast {
      pointer-events: auto !important;
      font-family: 'Open Sans', sans-serif !important;
      border: none !important;
      outline: none !important;
    }

    .fallback-toast.fallback-success {
      background: linear-gradient(135deg, #198754 0%, #20c997 100%) !important;
      color: white !important;
    }

    .fallback-toast.fallback-error {
      background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%) !important;
      color: white !important;
    }

    .fallback-toast.fallback-warning {
      background: linear-gradient(135deg, #fd7e14 0%, #f39c12 100%) !important;
      color: white !important;
    }

    .fallback-toast.fallback-info {
      background: linear-gradient(135deg, #0dcaf0 0%, #3498db 100%) !important;
      color: white !important;
    }

    .fallback-toast:hover {
      transform: translateX(-2px) !important;
      box-shadow: 0 6px 25px rgba(0,0,0,0.25) !important;
    }
  </style>
</head>

<body class="g-sidenav-show  bg-gray-100">
  @auth
    @yield('auth')
  @endauth
  @guest
    @yield('guest')
  @endguest

  <!--   Core JS Files   -->
  <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
  <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/fullcalendar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
  @stack('dashboard')
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>

  <!-- Notification System -->
  <script src="{{ asset('js/notifications.js?v=' . time()) }}"></script>
  
  <!-- Session Notifications -->
  @if(session()->has('success'))
    <script>
      (function() {
        var successMessage = {{ json_encode(session('success')) }};
        console.log('🟢 Mensaje de éxito detectado:', successMessage);
        
        // Como notifications.js ya está cargado, debería estar disponible
        if (typeof NotificationSystem !== 'undefined') {
          console.log('✅ NotificationSystem disponible inmediatamente');
          NotificationSystem.success(successMessage, 6000);
        } else {
          // Si no está, esperamos un poco (en caso de que esté inicializándose)
          console.warn('⚠️ NotificationSystem no disponible, esperando...');
          setTimeout(function() {
            if (typeof NotificationSystem !== 'undefined') {
              NotificationSystem.success(successMessage, 6000);
            } else {
              console.error('❌ NotificationSystem nunca se cargó - usando alert');
              alert(successMessage);
            }
          }, 500);
        }
      })();
    </script>
  @endif
  
  @if(session()->has('error'))
    <script>
      (function() {
        var errorMessage = {{ json_encode(session('error')) }};
        console.log('🔴 Mensaje de error detectado:', errorMessage);
        
        if (typeof NotificationSystem !== 'undefined') {
          NotificationSystem.error(errorMessage, 5000);
        } else {
          setTimeout(function() {
            if (typeof NotificationSystem !== 'undefined') {
              NotificationSystem.error(errorMessage, 5000);
            } else {
              alert(errorMessage);
            }
          }, 500);
        }
      })();
    </script>
  @endif
  
  @if(session()->has('warning'))
    <script>
      (function() {
        var warningMessage = {{ json_encode(session('warning')) }};
        console.log('🟡 Mensaje de advertencia detectado:', warningMessage);
        
        if (typeof NotificationSystem !== 'undefined') {
          NotificationSystem.warning(warningMessage, 5000);
        } else {
          setTimeout(function() {
            if (typeof NotificationSystem !== 'undefined') {
              NotificationSystem.warning(warningMessage, 5000);
            } else {
              alert(warningMessage);
            }
          }, 500);
        }
      })();
    </script>
  @endif
  
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="{{ asset('assets/js/soft-ui-dashboard.min.js?v=1.0.3') }}"></script>
  @stack('scripts')
</body>

</html>
