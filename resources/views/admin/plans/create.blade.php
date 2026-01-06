@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Crear Nuevo Plan</h5>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <form action="{{ route('admin.plans.store') }}" method="POST" class="p-4">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipo</label>
                        <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="monthly" {{ old('type') === 'monthly' ? 'selected' : '' }}>Mensual</option>
                            <option value="yearly" {{ old('type') === 'yearly' ? 'selected' : '' }}>Anual</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="duration_months" class="form-label">Duración (meses)</label>
                        <input type="number" class="form-control @error('duration_months') is-invalid @enderror" id="duration_months" name="duration_months" value="{{ old('duration_months') }}" required>
                        @error('duration_months')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Activo
                            </label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn bg-gradient-primary">Crear Plan</button>
                        <a href="{{ route('admin.plans.index') }}" class="btn bg-gradient-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

