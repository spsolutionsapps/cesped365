@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Estado Suscripción</p>
                            <h5 class="font-weight-bolder mb-0">
                                @if($activeSubscription)
                                    <span class="text-success text-sm font-weight-bolder">{{ $activeSubscription->status === 'active' ? 'Activa' : ($activeSubscription->status === 'suspended' ? 'Suspendida' : ($activeSubscription->status === 'expired' ? 'Expirada' : 'Cancelada')) }}</span>
                                @else
                                    <span class="text-danger text-sm font-weight-bolder">Sin Suscripción</span>
                                @endif
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary text-center border-radius-md">
                            <i class="ni ni-credit-card text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Plan Actual</p>
                            <h5 class="font-weight-bolder mb-0">
                                @if($activeSubscription)
                                    {{ $activeSubscription->plan->name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-success text-center border-radius-md">
                            <i class="ni ni-calendar-grid-58 text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Próxima Visita</p>
                            <h5 class="font-weight-bolder mb-0">
                                @if($activeSubscription)
                                    {{ \Carbon\Carbon::parse($activeSubscription->end_date)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-info text-center border-radius-md">
                            <i class="ni ni-watch-time text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Último Reporte</p>
                            <h5 class="font-weight-bolder mb-0">
                                @if($lastReport)
                                    {{ \Carbon\Carbon::parse($lastReport->report_date)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Sin reportes</span>
                                @endif
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-warning text-center border-radius-md">
                            <i class="ni ni-folder-17 text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-7 mb-lg-0 mb-4">
        <div class="card">
            <div class="card-header pb-0">
                <h6>Mi Suscripción</h6>
            </div>
            <div class="card-body p-3">
                @if($activeSubscription)
                    <div class="d-flex flex-column">
                        <h5 class="font-weight-bolder">{{ $activeSubscription->plan->name }}</h5>
                        <p class="mb-2">
                            <strong>Tipo:</strong> {{ $activeSubscription->plan->type === 'monthly' ? 'Mensual' : 'Anual' }}<br>
                            <strong>Precio:</strong> ${{ number_format($activeSubscription->plan->price, 2, ',', '.') }}<br>
                            <strong>Inicio:</strong> {{ \Carbon\Carbon::parse($activeSubscription->start_date)->format('d/m/Y') }}<br>
                            <strong>Fin:</strong> {{ \Carbon\Carbon::parse($activeSubscription->end_date)->format('d/m/Y') }}<br>
                            <strong>Estado:</strong> 
                            <span class="badge bg-{{ $activeSubscription->status === 'active' ? 'success' : ($activeSubscription->status === 'suspended' ? 'warning' : 'danger') }}">
                                {{ $activeSubscription->status === 'active' ? 'Activa' : ($activeSubscription->status === 'suspended' ? 'Suspendida' : ($activeSubscription->status === 'expired' ? 'Expirada' : 'Cancelada')) }}
                            </span>
                        </p>
                        <a href="{{ route('dashboard.subscription') }}" class="btn btn-primary btn-sm mt-2">Ver Detalles</a>
                    </div>
                @else
                    <p class="text-muted">No tienes una suscripción activa. <a href="{{ route('landing.plans') }}">Ver planes disponibles</a></p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header pb-0">
                <h6>Último Reporte</h6>
            </div>
            <div class="card-body p-3">
                @if($lastReport)
                    <div class="d-flex flex-column">
                        <h6 class="font-weight-bolder mb-2">Fecha: {{ \Carbon\Carbon::parse($lastReport->report_date)->format('d/m/Y') }}</h6>
                        <p class="mb-2">
                            <strong>Estado General:</strong> 
                            <span class="badge bg-{{ $lastReport->general_status === 'good' ? 'success' : ($lastReport->general_status === 'regular' ? 'warning' : 'danger') }}">
                                {{ $lastReport->general_status === 'good' ? 'Bueno' : ($lastReport->general_status === 'regular' ? 'Regular' : 'Mejorar') }}
                            </span>
                        </p>
                        <p class="mb-2">
                            <strong>Crecimiento:</strong> {{ $lastReport->growth_cm }} cm ({{ $lastReport->growth_category === 'low' ? 'Bajo' : ($lastReport->growth_category === 'normal' ? 'Normal' : 'Alto') }})
                        </p>
                        @if($lastReport->images->count() > 0)
                            <div class="mt-2">
                                <small class="text-muted">{{ $lastReport->images->count() }} imagen(es)</small>
                            </div>
                        @endif
                        <a href="{{ route('dashboard.garden-reports') }}" class="btn btn-primary btn-sm mt-2">Ver Todos los Reportes</a>
                    </div>
                @else
                    <p class="text-muted">Aún no hay reportes disponibles.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

