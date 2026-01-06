
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 " id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="align-items-center d-flex m-0 navbar-brand text-wrap" href="{{ route('dashboard') }}">
        <i class="fas fa-seedling text-success me-2" style="font-size: 1.5rem;"></i>
        <span class="ms-2 font-weight-bold">Césped 365</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0">
  <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('dashboard') && !Request::is('dashboard/*') ? 'active' : '') }}" href="{{ route('dashboard') }}">
          <div class="icon icon-shape icon-sm border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>

      @if(auth()->user()->isAdmin())
        {{-- MENÚ ADMINISTRADOR --}}
        <li class="nav-item">
          <a class="nav-link {{ (Request::is('admin/users*') ? 'active' : '') }}" href="{{ route('admin.users.index') }}">
            <div class="icon icon-shape icon-sm border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Usuarios</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ (Request::is('admin/plans*') ? 'active' : '') }}" href="{{ route('admin.plans.index') }}">
            <div class="icon icon-shape icon-sm border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-calendar-grid-58 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Planes</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ (Request::is('admin/subscriptions*') ? 'active' : '') }}" href="{{ route('admin.subscriptions.index') }}">
            <div class="icon icon-shape icon-sm border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-credit-card text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Suscripciones</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ (Request::is('admin/garden-reports*') ? 'active' : '') }}" href="{{ route('admin.garden-reports.index') }}">
            <div class="icon icon-shape icon-sm border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-folder-17 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Reportes del Jardín</span>
          </a>
        </li>
      @else
        {{-- MENÚ CLIENTE --}}
        <li class="nav-item mt-2">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Mi Cuenta</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ (Request::is('dashboard/subscription') ? 'active' : '') }}" href="{{ route('dashboard.subscription') }}">
            <div class="icon icon-shape icon-sm border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-credit-card text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Mi Suscripción</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ (Request::is('dashboard/reports*') || Request::is('dashboard/garden-reports*') ? 'active' : '') }}" href="{{ route('dashboard.reports') }}">
            <div class="icon icon-shape icon-sm border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-folder-17 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Reportes del Jardín</span>
          </a>
        </li>
      @endif

      <li class="nav-item mt-3" style="display: none;">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Cuenta</h6>
      </li>
      <li class="nav-item" style="display: none;">
        <a class="nav-link {{ (Request::is('profile') || Request::is('user-profile') ? 'active' : '') }}" href="{{ route('profile') }}">
          <div class="icon icon-shape icon-sm border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-single-02 text-primary text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Perfil</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('logout') }}">
          <div class="icon icon-shape icon-sm border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-button-power text-primary text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Cerrar Sesión</span>
        </a>
      </li>
    </ul>
  </div>
</aside>
