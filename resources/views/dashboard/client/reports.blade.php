@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <h5 class="mb-0">Mis Reportes por Mes</h5>
                    <a href="{{ route('dashboard') }}" class="btn bg-gradient-secondary btn-sm mb-0">
                        Volver al Dashboard
                    </a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                @if($reports->count() > 0)
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mes y Año</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Visitas</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado General</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $month)
                                <tr>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ ucfirst($month['month_name']) }} {{ $month['year'] }}
                                        </p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $month['count'] }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-sm bg-{{ $month['status'] === 'good' ? 'success' : ($month['status'] === 'regular' ? 'warning' : 'danger') }}">
                                            {{ $month['status'] === 'good' ? 'Bueno' : ($month['status'] === 'regular' ? 'Regular' : 'Mejorar') }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('dashboard.reports.month', ['year' => $month['year'], 'month' => $month['month']]) }}" class="text-secondary font-weight-bold text-xs">
                                            Ver Visitas del Mes
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center">
                        <p class="text-muted">Aún no hay reportes registrados.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection




