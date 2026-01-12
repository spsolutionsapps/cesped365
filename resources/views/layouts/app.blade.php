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
</head>

<body class="g-sidenav-show  bg-gray-100">
  @auth
    @yield('auth')
  @endauth
  @guest
    @yield('guest')
  @endguest

  @if(session()->has('success'))
    <!-- Modal de Éxito -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 bg-gradient-success text-white">
            <h5 class="modal-title d-flex align-items-center" id="successModalLabel">
              <i class="ni ni-check-bold me-2"></i>Éxito
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body text-center py-4 px-4">
            <div class="mb-3">
              <i class="ni ni-check-bold text-success" style="font-size: 3.5rem;"></i>
            </div>
            <p class="mb-0 fs-6 fw-normal">{{ session('success') }}</p>
          </div>
          <div class="modal-footer border-0 justify-content-center pb-4">
            <button type="button" class="btn bg-gradient-success text-white px-4" data-bs-dismiss="modal" aria-label="Aceptar">Aceptar</button>
          </div>
        </div>
      </div>
    </div>
    
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var successModalElement = document.getElementById('successModal');
        if (successModalElement) {
          var successModal = new bootstrap.Modal(successModalElement, {
            backdrop: 'static',
            keyboard: false,
            focus: true
          });
          
          // Manejar eventos del modal para corregir aria-hidden
          successModalElement.addEventListener('shown.bs.modal', function() {
            // Asegurar que aria-hidden esté correctamente configurado
            successModalElement.setAttribute('aria-hidden', 'false');
            // Enfocar el botón de aceptar
            var acceptBtn = successModalElement.querySelector('[data-bs-dismiss="modal"]');
            if (acceptBtn) {
              acceptBtn.focus();
            }
          });
          
          successModalElement.addEventListener('hide.bs.modal', function() {
            // Restaurar aria-hidden antes de ocultar
            successModalElement.setAttribute('aria-hidden', 'true');
          });
          
          successModal.show();
          
          // Cerrar automáticamente después de 3 segundos
          setTimeout(function() {
            successModal.hide();
          }, 3000);
          
          // Limpiar el modal del DOM cuando se cierre
          successModalElement.addEventListener('hidden.bs.modal', function () {
            successModalElement.remove();
          });
        }
      });
    </script>
  @endif
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

  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="{{ asset('assets/js/soft-ui-dashboard.min.js?v=1.0.3') }}"></script>
  @stack('scripts')
</body>

</html>
