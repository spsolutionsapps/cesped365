@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Editar Suscripción</h5>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <form action="{{ route('admin.subscriptions.update', $subscription) }}" method="POST" class="p-4">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Usuario</label>
                        <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">Seleccionar usuario</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $subscription->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="plan_id" class="form-label">Plan</label>
                        <select class="form-control @error('plan_id') is-invalid @enderror" id="plan_id" name="plan_id" required>
                            <option value="">Seleccionar plan</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id', $subscription->plan_id) == $plan->id ? 'selected' : '' }}>{{ $plan->name }} - ${{ number_format($plan->price, 2, ',', '.') }}</option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $subscription->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $subscription->status) === 'active' ? 'selected' : '' }}>Activa</option>
                            <option value="suspended" {{ old('status', $subscription->status) === 'suspended' ? 'selected' : '' }}>Suspendida</option>
                            <option value="expired" {{ old('status', $subscription->status) === 'expired' ? 'selected' : '' }}>Expirada</option>
                            <option value="cancelled" {{ old('status', $subscription->status) === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn bg-gradient-primary">Actualizar Suscripción</button>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn bg-gradient-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

