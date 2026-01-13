@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <h6>Reportes del Jardín</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                @forelse($reports as $report)
                <div class="card m-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="card-title">Reporte del {{ \Carbon\Carbon::parse($report->report_date)->format('d/m/Y') }}</h5>
                                <p class="card-text">
                                    <strong>Estado General:</strong> 
                                    <span class="badge bg-{{ $report->general_status === 'good' ? 'success' : ($report->general_status === 'regular' ? 'warning' : 'danger') }}">
                                        {{ $report->general_status === 'good' ? 'Bueno' : ($report->general_status === 'regular' ? 'Regular' : 'Mejorar') }}
                                    </span>
                                </p>
                                <p class="card-text">
                                    <strong>Césped:</strong> 
                                    Color: {{ $report->grass_color === 'ok' ? 'Bueno' : ($report->grass_color === 'regular' ? 'Regular' : 'Malo') }} | 
                                    @if($report->grass_even) Uniforme @else No uniforme @endif |
                                    @if($report->visible_weeds) <span class="text-danger">Con malezas</span> @else Sin malezas @endif
                                </p>
                                <p class="card-text">
                                    <strong>Crecimiento:</strong> {{ $report->growth_cm }} cm ({{ $report->growth_category === 'low' ? 'Bajo' : ($report->growth_category === 'normal' ? 'Normal' : 'Alto') }})
                                </p>
                                <p class="card-text">
                                    <strong>Suelo:</strong> {{ $report->soil_condition === 'loose' ? 'Suelto' : 'Compacto' }} | 
                                    @if($report->aeration_recommended) <span class="text-warning">Aireación recomendada</span> @endif
                                </p>
                                <p class="card-text">
                                    <strong>Humedad:</strong> {{ $report->humidity_status === 'dry' ? 'Seco' : ($report->humidity_status === 'correct' ? 'Correcto' : 'Exceso') }} | 
                                    <strong>Plagas:</strong> {{ $report->pests_status === 'none' ? 'Ninguna' : ($report->pests_status === 'mild' ? 'Leve' : 'Observar') }} | 
                                    <strong>Canteros:</strong> {{ $report->flowerbeds_status === 'clean' ? 'Limpio' : ($report->flowerbeds_status === 'weeds' ? 'Con malezas' : 'Mantenimiento') }}
                                </p>
                                @if($report->general_observations)
                                    <p class="card-text"><strong>Observaciones:</strong> {{ $report->general_observations }}</p>
                                @endif
                            </div>
                            <div class="col-md-4">
                                @if($report->images->count() > 0)
                                    <h6>Imágenes:</h6>
                                    <div class="row">
                                        @foreach($report->images->take(3) as $image)
                                        <div class="col-4 mb-2">
                                            <img src="{{ $image->public_url }}" class="img-fluid rounded" alt="Reporte">
                                        </div>
                                        @endforeach
                                    </div>
                                    @if($report->images->count() > 3)
                                        <small class="text-muted">+{{ $report->images->count() - 3 }} más</small>
                                    @endif
                                @else
                                    <p class="text-muted">Sin imágenes</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <p class="text-muted">Aún no hay reportes disponibles.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

