@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Editar Reporte del Jardín</h5>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <form action="{{ route('admin.garden-reports.update', $gardenReport) }}" method="POST" enctype="multipart/form-data" class="p-4">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Usuario</label>
                        <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $gardenReport->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Suscripción actual: <strong>{{ $gardenReport->subscription->plan->name }}</strong> 
                            ({{ $gardenReport->subscription->status === 'active' ? 'Activa' : ($gardenReport->subscription->status === 'suspended' ? 'Suspendida' : ($gardenReport->subscription->status === 'expired' ? 'Expirada' : 'Cancelada')) }}). 
                            Si cambia el usuario, se utilizará automáticamente su suscripción activa.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="report_date" class="form-label">Fecha del Reporte</label>
                        <input type="date" class="form-control @error('report_date') is-invalid @enderror" id="report_date" name="report_date" value="{{ old('report_date', $gardenReport->report_date->format('Y-m-d')) }}" required>
                        @error('report_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="general_status" class="form-label">Estado General</label>
                        <select class="form-control @error('general_status') is-invalid @enderror" id="general_status" name="general_status" required>
                            <option value="good" {{ old('general_status', $gardenReport->general_status) === 'good' ? 'selected' : '' }}>Bueno</option>
                            <option value="regular" {{ old('general_status', $gardenReport->general_status) === 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="improve" {{ old('general_status', $gardenReport->general_status) === 'improve' ? 'selected' : '' }}>Mejorar</option>
                        </select>
                        @error('general_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="images" class="form-label">Agregar Imágenes</label>
                        <input type="file" class="form-control @error('images') is-invalid @enderror" id="images" name="images[]" multiple accept="image/*">
                        @error('images')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn bg-gradient-primary">Actualizar Reporte</button>
                        <a href="{{ route('admin.garden-reports.index') }}" class="btn bg-gradient-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

