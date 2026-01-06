@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    {{-- Bloque 1: Estado actual del jardín --}}
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Estado Actual del Jardín</h5>
            </div>
            <div class="card-body p-3">
                @if($latestReport)
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-2">Última Visita</h6>
                            <p class="mb-1">
                                <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($latestReport->report_date)->format('d/m/Y') }}
                            </p>
                            <p class="mb-1">
                                <strong>Estado General:</strong>
                                <span class="badge badge-sm bg-{{ $latestReport->general_status === 'good' ? 'success' : ($latestReport->general_status === 'regular' ? 'warning' : 'danger') }}">
                                    {{ $latestReport->general_status === 'good' ? 'Bueno' : ($latestReport->general_status === 'regular' ? 'Regular' : 'Mejorar') }}
                                </span>
                            </p>
                            <p class="mb-0">
                                <strong>Próxima Visita Estimada:</strong> 
                                {{ \Carbon\Carbon::parse($latestReport->report_date)->addDays(7)->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('dashboard.garden-reports.show', $latestReport->id) }}" class="btn bg-gradient-primary btn-sm">
                                Ver Reporte Completo
                            </a>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0">Aún no hay reportes disponibles. Tu primer reporte aparecerá aquí después de la primera visita.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Bloque 2: Qué tengo que saber hoy --}}
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Qué tengo que saber hoy</h5>
            </div>
            <div class="card-body p-3">
                @if($latestReport)
                    @php
                        $notes = array_filter([
                            $latestReport->grass_note ? 'Césped: ' . $latestReport->grass_note : null,
                            $latestReport->growth_note ? 'Crecimiento: ' . $latestReport->growth_note : null,
                            $latestReport->soil_note ? 'Suelo: ' . $latestReport->soil_note : null,
                            $latestReport->humidity_note ? 'Humedad: ' . $latestReport->humidity_note : null,
                            $latestReport->pests_note ? 'Plagas: ' . $latestReport->pests_note : null,
                            $latestReport->flowerbeds_note ? 'Canteros: ' . $latestReport->flowerbeds_note : null,
                            $latestReport->general_observations ? 'Observaciones: ' . $latestReport->general_observations : null,
                        ]);
                    @endphp
                    @if(count($notes) > 0)
                        <ul class="list-unstyled mb-0">
                            @foreach($notes as $note)
                                <li class="mb-2">
                                    <i class="ni ni-notification-70 text-primary me-2"></i>
                                    {{ $note }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">
                            <i class="ni ni-check-bold text-success me-2"></i>
                            No hay observaciones relevantes en el último reporte.
                        </p>
                    @endif
                @else
                    <p class="text-muted mb-0">No hay observaciones disponibles aún.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Bloque 3: Indicadores rápidos --}}
    @if($latestReport)
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body p-3 text-center">
                <h6 class="mb-2">Césped</h6>
                <span class="badge badge-lg bg-{{ $latestReport->grass_color === 'ok' ? 'success' : ($latestReport->grass_color === 'regular' ? 'warning' : 'danger') }}">
                    {{ $latestReport->grass_color === 'ok' ? 'Bueno' : ($latestReport->grass_color === 'regular' ? 'Regular' : 'Malo') }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body p-3 text-center">
                <h6 class="mb-2">Riego</h6>
                <span class="badge badge-lg bg-{{ $latestReport->humidity_status === 'correct' ? 'success' : ($latestReport->humidity_status === 'dry' ? 'warning' : 'danger') }}">
                    {{ $latestReport->humidity_status === 'dry' ? 'Seco' : ($latestReport->humidity_status === 'correct' ? 'Correcto' : 'Exceso') }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body p-3 text-center">
                <h6 class="mb-2">Plagas</h6>
                <span class="badge badge-lg bg-{{ $latestReport->pests_status === 'none' ? 'success' : ($latestReport->pests_status === 'mild' ? 'warning' : 'info') }}">
                    {{ $latestReport->pests_status === 'none' ? 'Ninguna' : ($latestReport->pests_status === 'mild' ? 'Leve' : 'Observar') }}
                </span>
            </div>
        </div>
    </div>
    @endif

    {{-- Bloque 4: Evolución mensual --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <h5 class="mb-0">Evolución Mensual</h5>
                    <a href="{{ route('dashboard.reports') }}" class="btn bg-gradient-primary btn-sm mb-0">
                        Ver Todos los Meses
                    </a>
                </div>
            </div>
            <div class="card-body p-3">
                @if($monthlyReports->count() > 0)
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mes</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Visitas</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyReports as $month)
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
                                            Ver Detalle
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Aún no hay reportes mensuales disponibles.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


