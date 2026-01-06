@extends('layouts.user_type.auth')

@section('content')

{{-- Primera fila: Métricas Financieras --}}
<div class="row mb-4">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Ganancias del Mes</p>
                            <h5 class="font-weight-bolder mb-0">
                                ${{ number_format($stats['monthly_revenue'], 2) }}
                            </h5>
                            <p class="mb-0">
                                <span class="text-success text-sm font-weight-bolder">+{{ $stats['new_users_this_month'] }}</span>
                                <span class="text-sm">nuevos usuarios</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-success text-center border-radius-md">
                            <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
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
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Ingresos Recurrentes (MRR)</p>
                            <h5 class="font-weight-bolder mb-0">
                                ${{ number_format($stats['mrr'], 2) }}
                            </h5>
                            <p class="mb-0">
                                <span class="text-sm">{{ $stats['active_subscriptions'] }} suscripciones</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-info text-center border-radius-md">
                            <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
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
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Pagos Pendientes</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $stats['pending_payments'] }}
                            </h5>
                            <p class="mb-0">
                                <span class="text-warning text-sm font-weight-bolder">${{ number_format($stats['pending_payments_amount'], 2) }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-warning text-center border-radius-md">
                            <i class="ni ni-time-alarm text-lg opacity-10" aria-hidden="true"></i>
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
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Reportes</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $stats['total_reports'] }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary text-center border-radius-md">
                            <i class="ni ni-folder-17 text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Segunda fila: Métricas de Usuarios y Suscripciones --}}
<div class="row mb-4">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Usuarios Totales</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $stats['total_users'] }}
                            </h5>
                            <p class="mb-0">
                                <span class="text-success text-sm font-weight-bolder">+{{ $stats['new_users_this_month'] }}</span>
                                <span class="text-sm">este mes</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary text-center border-radius-md">
                            <i class="ni ni-single-02 text-lg opacity-10" aria-hidden="true"></i>
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
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Suscripciones Activas</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $stats['active_subscriptions'] }}
                            </h5>
                            <p class="mb-0">
                                <span class="text-danger text-sm font-weight-bolder">{{ $stats['expiring_urgent'] }}</span>
                                <span class="text-sm">por vencer (7 días)</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-success text-center border-radius-md">
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
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Por Vencer (30 días)</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $stats['expiring_soon'] }}
                            </h5>
                            <p class="mb-0">
                                <span class="text-warning text-sm font-weight-bolder">{{ $stats['expiring_urgent'] }}</span>
                                <span class="text-sm">urgentes</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-warning text-center border-radius-md">
                            <i class="ni ni-calendar-grid-58 text-lg opacity-10" aria-hidden="true"></i>
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
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Expiradas Recientes</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $stats['recently_expired'] }}
                            </h5>
                            <p class="mb-0">
                                <span class="text-sm">Últimos 30 días</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-danger text-center border-radius-md">
                            <i class="ni ni-fat-remove text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tercera fila: Desglose de Ganancias por Plan --}}
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header pb-0">
                <h6>Ganancias por Plan (Este Mes)</h6>
            </div>
            <div class="card-body p-3">
                @if($stats['revenue_by_plan']->count() > 0)
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Plan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Ganancias</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">% del Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['revenue_by_plan'] as $planName => $amount)
                                <tr>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $planName }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-success">${{ number_format($amount, 2) }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        @php
                                            $percentage = $stats['monthly_revenue'] > 0 
                                                ? round(($amount / $stats['monthly_revenue']) * 100, 1) 
                                                : 0;
                                        @endphp
                                        <span class="text-secondary text-xs font-weight-bold">{{ $percentage }}%</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-muted mb-0">No hay ganancias registradas este mes.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Cuarta fila: Accesos Rápidos --}}
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header pb-0">
                <h6>Accesos Rápidos</h6>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary w-100">
                            <i class="ni ni-single-02 me-2"></i> Gestionar Usuarios
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-success w-100">
                            <i class="ni ni-calendar-grid-58 me-2"></i> Gestionar Planes
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-info w-100">
                            <i class="ni ni-credit-card me-2"></i> Gestionar Suscripciones
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.garden-reports.index') }}" class="btn btn-warning w-100">
                            <i class="ni ni-folder-17 me-2"></i> Gestionar Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

