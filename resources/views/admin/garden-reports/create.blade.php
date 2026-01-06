@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Crear Nuevo Reporte del Jardín</h5>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <form action="{{ route('admin.garden-reports.store') }}" method="POST" enctype="multipart/form-data" class="p-4">
                    @csrf
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Usuario</label>
                        <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">Seleccionar usuario</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Se utilizará automáticamente la suscripción activa del usuario.</small>
                    </div>
                    <div class="mb-3">
                        <label for="report_date" class="form-label">Fecha del Reporte</label>
                        <input type="date" class="form-control @error('report_date') is-invalid @enderror" id="report_date" name="report_date" value="{{ old('report_date', date('Y-m-d')) }}" required>
                        @error('report_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="general_status" class="form-label">Estado General</label>
                        <select class="form-control @error('general_status') is-invalid @enderror" id="general_status" name="general_status" required>
                            <option value="good" {{ old('general_status') === 'good' ? 'selected' : '' }}>Bueno</option>
                            <option value="regular" {{ old('general_status') === 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="improve" {{ old('general_status') === 'improve' ? 'selected' : '' }}>Mejorar</option>
                        </select>
                        @error('general_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="grass_color" class="form-label">Color del Césped</label>
                            <select class="form-control @error('grass_color') is-invalid @enderror" id="grass_color" name="grass_color" required>
                                <option value="ok" {{ old('grass_color') === 'ok' ? 'selected' : '' }}>Bueno</option>
                                <option value="regular" {{ old('grass_color') === 'regular' ? 'selected' : '' }}>Regular</option>
                                <option value="bad" {{ old('grass_color') === 'bad' ? 'selected' : '' }}>Malo</option>
                            </select>
                            @error('grass_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="growth_cm" class="form-label">Crecimiento (cm)</label>
                            <input type="number" step="0.1" class="form-control @error('growth_cm') is-invalid @enderror" id="growth_cm" name="growth_cm" value="{{ old('growth_cm') }}" required>
                            @error('growth_cm')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="growth_category" class="form-label">Categoría de Crecimiento</label>
                            <select class="form-control @error('growth_category') is-invalid @enderror" id="growth_category" name="growth_category" required>
                                <option value="low" {{ old('growth_category') === 'low' ? 'selected' : '' }}>Bajo</option>
                                <option value="normal" {{ old('growth_category') === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('growth_category') === 'high' ? 'selected' : '' }}>Alto</option>
                            </select>
                            @error('growth_category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="soil_condition" class="form-label">Condición del Suelo</label>
                            <select class="form-control @error('soil_condition') is-invalid @enderror" id="soil_condition" name="soil_condition" required>
                                <option value="loose" {{ old('soil_condition') === 'loose' ? 'selected' : '' }}>Suelto</option>
                                <option value="compact" {{ old('soil_condition') === 'compact' ? 'selected' : '' }}>Compacto</option>
                            </select>
                            @error('soil_condition')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="humidity_status" class="form-label">Estado de Humedad</label>
                            <select class="form-control @error('humidity_status') is-invalid @enderror" id="humidity_status" name="humidity_status" required>
                                <option value="dry" {{ old('humidity_status') === 'dry' ? 'selected' : '' }}>Seco</option>
                                <option value="correct" {{ old('humidity_status') === 'correct' ? 'selected' : '' }}>Correcto</option>
                                <option value="excess" {{ old('humidity_status') === 'excess' ? 'selected' : '' }}>Exceso</option>
                            </select>
                            @error('humidity_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="pests_status" class="form-label">Estado de Plagas</label>
                            <select class="form-control @error('pests_status') is-invalid @enderror" id="pests_status" name="pests_status" required>
                                <option value="none" {{ old('pests_status') === 'none' ? 'selected' : '' }}>Ninguna</option>
                                <option value="mild" {{ old('pests_status') === 'mild' ? 'selected' : '' }}>Leve</option>
                                <option value="observe" {{ old('pests_status') === 'observe' ? 'selected' : '' }}>Observar</option>
                            </select>
                            @error('pests_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="flowerbeds_status" class="form-label">Estado de Canteros</label>
                            <select class="form-control @error('flowerbeds_status') is-invalid @enderror" id="flowerbeds_status" name="flowerbeds_status" required>
                                <option value="clean" {{ old('flowerbeds_status') === 'clean' ? 'selected' : '' }}>Limpio</option>
                                <option value="weeds" {{ old('flowerbeds_status') === 'weeds' ? 'selected' : '' }}>Con malezas</option>
                                <option value="maintenance" {{ old('flowerbeds_status') === 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
                            </select>
                            @error('flowerbeds_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="general_observations" class="form-label">Observaciones Generales</label>
                        <textarea class="form-control @error('general_observations') is-invalid @enderror" id="general_observations" name="general_observations" rows="3">{{ old('general_observations') }}</textarea>
                        @error('general_observations')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="images" class="form-label">Imágenes</label>
                        <input type="file" class="form-control @error('images') is-invalid @enderror" id="images" name="images[]" multiple accept="image/*">
                        @error('images')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn bg-gradient-primary">Crear Reporte</button>
                        <a href="{{ route('admin.garden-reports.index') }}" class="btn bg-gradient-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

