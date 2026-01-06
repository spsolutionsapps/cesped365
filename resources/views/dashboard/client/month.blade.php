@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <h5 class="mb-0">Visitas de {{ ucfirst($monthName) }} {{ $year }}</h5>
                    <a href="{{ route('dashboard.reports') }}" class="btn bg-gradient-secondary btn-sm mb-0">
                        Volver a Meses
                    </a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                @if($reports->count() > 0)
                    <div class="p-4">
                        @foreach($reports as $report)
                        <div class="card mb-3">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <h6 class="mb-0">Fecha</h6>
                                        <p class="text-sm mb-0">{{ \Carbon\Carbon::parse($report->report_date)->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <h6 class="mb-0">Estado General</h6>
                                        <span class="badge badge-sm bg-{{ $report->general_status === 'good' ? 'success' : ($report->general_status === 'regular' ? 'warning' : 'danger') }}">
                                            {{ $report->general_status === 'good' ? 'Bueno' : ($report->general_status === 'regular' ? 'Regular' : 'Mejorar') }}
                                        </span>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <h6 class="mb-0">Imágenes</h6>
                                        <p class="text-sm mb-0">{{ $report->images->count() }} foto(s)</p>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="{{ route('dashboard.garden-reports.show', $report->id) }}" class="btn bg-gradient-primary btn-sm">
                                            Ver Reporte Completo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 text-center">
                        <p class="text-muted">No hay reportes para este mes.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


