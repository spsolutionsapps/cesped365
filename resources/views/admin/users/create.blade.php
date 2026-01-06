@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Crear Nuevo Usuario</h5>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <form action="{{ route('admin.users.store') }}" method="POST" class="p-4">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Rol</label>
                        <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="client" {{ old('role') === 'client' ? 'selected' : '' }}>Cliente</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn bg-gradient-primary">Crear Usuario</button>
                        <a href="{{ route('admin.users.index') }}" class="btn bg-gradient-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

