@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <h5 class="mb-0">Reporte Completo - {{ \Carbon\Carbon::parse($report->report_date)->format('d/m/Y') }}</h5>
                    <a href="{{ route('dashboard.reports.month', ['year' => \Carbon\Carbon::parse($report->report_date)->year, 'month' => \Carbon\Carbon::parse($report->report_date)->month]) }}" class="btn bg-gradient-secondary btn-sm mb-0">
                        Volver al Mes
                    </a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="p-4">
                    {{-- Información General --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Información General</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Fecha del Reporte:</strong> {{ \Carbon\Carbon::parse($report->report_date)->format('d/m/Y') }}<br>
                                <strong>Estado General:</strong>
                                <span class="badge badge-sm bg-{{ $report->general_status === 'good' ? 'success' : ($report->general_status === 'regular' ? 'warning' : 'danger') }}">
                                    {{ $report->general_status === 'good' ? 'Bueno' : ($report->general_status === 'regular' ? 'Regular' : 'Mejorar') }}
                                </span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Plan:</strong> {{ $report->subscription->plan->name }}<br>
                                <strong>Suscripción:</strong> {{ $report->subscription->status === 'active' ? 'Activa' : ($report->subscription->status === 'suspended' ? 'Suspendida' : ($report->subscription->status === 'expired' ? 'Expirada' : 'Cancelada')) }}
                            </div>
                        </div>
                    </div>

                    {{-- Césped --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Estado del Césped</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Color:</strong>
                                <span class="badge badge-sm bg-{{ $report->grass_color === 'ok' ? 'success' : ($report->grass_color === 'regular' ? 'warning' : 'danger') }}">
                                    {{ $report->grass_color === 'ok' ? 'Bueno' : ($report->grass_color === 'regular' ? 'Regular' : 'Malo') }}
                                </span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Uniformidad:</strong> {{ $report->grass_even ? 'Uniforme' : 'No uniforme' }}<br>
                                <strong>Manchas:</strong> {{ $report->grass_spots ? 'Sí' : 'No' }}<br>
                                <strong>Áreas desgastadas:</strong> {{ $report->worn_areas ? 'Sí' : 'No' }}<br>
                                <strong>Malezas visibles:</strong> {{ $report->visible_weeds ? 'Sí' : 'No' }}
                            </div>
                        </div>
                        @if($report->grass_note)
                            <div class="mt-2">
                                <strong>Nota:</strong> {{ $report->grass_note }}
                            </div>
                        @endif
                    </div>

                    {{-- Crecimiento --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Crecimiento</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Crecimiento:</strong> {{ $report->growth_cm }} cm<br>
                                <strong>Categoría:</strong>
                                <span class="badge badge-sm bg-{{ $report->growth_category === 'normal' ? 'success' : ($report->growth_category === 'low' ? 'warning' : 'info') }}">
                                    {{ $report->growth_category === 'low' ? 'Bajo' : ($report->growth_category === 'normal' ? 'Normal' : 'Alto') }}
                                </span>
                            </div>
                            @if($report->growth_estimated)
                            <div class="col-md-6 mb-2">
                                <strong>Crecimiento Estimado:</strong> {{ $report->growth_estimated }} cm
                            </div>
                            @endif
                        </div>
                        @if($report->growth_note)
                            <div class="mt-2">
                                <strong>Nota:</strong> {{ $report->growth_note }}
                            </div>
                        @endif
                    </div>

                    {{-- Suelo --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Estado del Suelo</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Condición:</strong> {{ $report->soil_condition === 'loose' ? 'Suelto' : 'Compacto' }}<br>
                                <strong>Aireación Recomendada:</strong> {{ $report->aeration_recommended ? 'Sí' : 'No' }}
                            </div>
                        </div>
                        @if($report->soil_note)
                            <div class="mt-2">
                                <strong>Nota:</strong> {{ $report->soil_note }}
                            </div>
                        @endif
                    </div>

                    {{-- Humedad --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Humedad</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Estado:</strong>
                                <span class="badge badge-sm bg-{{ $report->humidity_status === 'correct' ? 'success' : ($report->humidity_status === 'dry' ? 'warning' : 'danger') }}">
                                    {{ $report->humidity_status === 'dry' ? 'Seco' : ($report->humidity_status === 'correct' ? 'Correcto' : 'Exceso') }}
                                </span>
                            </div>
                        </div>
                        @if($report->humidity_note)
                            <div class="mt-2">
                                <strong>Nota:</strong> {{ $report->humidity_note }}
                            </div>
                        @endif
                    </div>

                    {{-- Plagas --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Plagas</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Estado:</strong>
                                <span class="badge badge-sm bg-{{ $report->pests_status === 'none' ? 'success' : ($report->pests_status === 'mild' ? 'warning' : 'info') }}">
                                    {{ $report->pests_status === 'none' ? 'Ninguna' : ($report->pests_status === 'mild' ? 'Leve' : 'Observar') }}
                                </span>
                            </div>
                        </div>
                        @if($report->pests_note)
                            <div class="mt-2">
                                <strong>Nota:</strong> {{ $report->pests_note }}
                            </div>
                        @endif
                    </div>

                    {{-- Canteros --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Canteros</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Estado:</strong>
                                <span class="badge badge-sm bg-{{ $report->flowerbeds_status === 'clean' ? 'success' : ($report->flowerbeds_status === 'weeds' ? 'warning' : 'info') }}">
                                    {{ $report->flowerbeds_status === 'clean' ? 'Limpio' : ($report->flowerbeds_status === 'weeds' ? 'Con malezas' : 'Mantenimiento') }}
                                </span>
                            </div>
                        </div>
                        @if($report->flowerbeds_note)
                            <div class="mt-2">
                                <strong>Nota:</strong> {{ $report->flowerbeds_note }}
                            </div>
                        @endif
                    </div>

                    {{-- Recomendaciones Estacionales --}}
                    @if($report->seasonal_recommendations)
                    <div class="mb-4">
                        <h6 class="mb-3">Recomendaciones Estacionales</h6>
                        <p>{{ $report->seasonal_recommendations }}</p>
                    </div>
                    @endif

                    {{-- Observaciones Generales --}}
                    @if($report->general_observations)
                    <div class="mb-4">
                        <h6 class="mb-3">Observaciones Generales</h6>
                        <p>{{ $report->general_observations }}</p>
                    </div>
                    @endif

                    {{-- Imágenes --}}
                    @if($report->images->count() > 0)
                    <div class="mb-4">
                        <h6 class="mb-3">Imágenes del Reporte</h6>
                        <div class="row">
                            @foreach($report->images as $image)
                            <div class="col-md-3 mb-3">
                                <img src="{{ asset('storage/app/public/' . $image->image_path) }}" class="img-fluid rounded" alt="Reporte del jardín">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

