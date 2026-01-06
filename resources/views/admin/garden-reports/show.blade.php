@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Detalles del Reporte</h5>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Usuario:</strong> {{ $gardenReport->user->name }}<br>
                            <strong>Email:</strong> {{ $gardenReport->user->email }}<br>
                            <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($gardenReport->report_date)->format('d/m/Y') }}<br>
                            <strong>Estado General:</strong> 
                            <span class="badge bg-{{ $gardenReport->general_status === 'good' ? 'success' : ($gardenReport->general_status === 'regular' ? 'warning' : 'danger') }}">
                                {{ $gardenReport->general_status === 'good' ? 'Bueno' : ($gardenReport->general_status === 'regular' ? 'Regular' : 'Mejorar') }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Plan:</strong> {{ $gardenReport->subscription->plan->name }}<br>
                            <strong>Crecimiento:</strong> {{ $gardenReport->growth_cm }} cm ({{ $gardenReport->growth_category === 'low' ? 'Bajo' : ($gardenReport->growth_category === 'normal' ? 'Normal' : 'Alto') }})<br>
                            <strong>Suelo:</strong> {{ $gardenReport->soil_condition === 'loose' ? 'Suelto' : 'Compacto' }}<br>
                            <strong>Humedad:</strong> {{ $gardenReport->humidity_status === 'dry' ? 'Seco' : ($gardenReport->humidity_status === 'correct' ? 'Correcto' : 'Exceso') }}
                        </div>
                    </div>
                    
                    @if($gardenReport->images->count() > 0)
                    <div class="mb-3">
                        <strong>Imágenes:</strong>
                        <div class="row mt-2">
                            @foreach($gardenReport->images as $image)
                            <div class="col-md-3 mb-2">
                                <img src="{{ asset('storage/' . $image->image_path) }}" class="img-fluid rounded" alt="Reporte">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($gardenReport->general_observations)
                    <div class="mb-3">
                        <strong>Observaciones Generales:</strong>
                        <p>{{ $gardenReport->general_observations }}</p>
                    </div>
                    @endif
                    
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.garden-reports.edit', $gardenReport) }}" class="btn bg-gradient-primary">Editar</a>
                        <a href="{{ route('admin.garden-reports.index') }}" class="btn bg-gradient-secondary">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

