@extends('layouts.user_type.auth')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <h6>Mis Suscripciones</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Plan</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tipo</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Inicio</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fin</th>
                                <th class="text-secondary opacity-7"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $subscription)
                            <tr>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $subscription->plan->name }}</h6>
                                            <p class="text-xs text-secondary mb-0">${{ number_format($subscription->plan->price, 2, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $subscription->plan->type === 'monthly' ? 'Mensual' : 'Anual' }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <span class="badge badge-sm bg-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'suspended' ? 'warning' : 'danger') }}">
                                        {{ $subscription->status === 'active' ? 'Activa' : ($subscription->status === 'suspended' ? 'Suspendida' : ($subscription->status === 'expired' ? 'Expirada' : 'Cancelada')) }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ \Carbon\Carbon::parse($subscription->start_date)->format('d/m/Y') }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ \Carbon\Carbon::parse($subscription->end_date)->format('d/m/Y') }}</span>
                                </td>
                                <td class="align-middle">
                                    <a href="javascript:;" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Ver detalles">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-muted">No tienes suscripciones aún. <a href="{{ route('landing.plans') }}">Ver planes disponibles</a></p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

