@extends('layouts.app')

@section('title', $user->display_name)
@section('page-title', $user->display_name)
@section('page-subtitle', 'Ficha del personal · ' . ($user->role?->label() ?? ''))

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('users.index') }}" class="btn btn-ghost btn-sm">← Volver al listado</a>
        @unless($user->trashed())
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">Editar</a>
            <a href="{{ route('audit.index', ['row_id' => $user->id]) }}" class="btn btn-outline btn-sm">Ver auditoría completa</a>
        @endunless
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <x-ui.avatar :name="$user->display_name" size="lg" ring />
                    <div>
                        <h2 class="text-xl font-semibold">{{ $user->display_name }}</h2>
                        <p class="text-sm text-base-content/60">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    @if ($user->trashed())
                        <span class="badge badge-error badge-outline">Eliminado</span>
                    @elseif ($user->isPendingActivation())
                        <span class="badge badge-warning badge-outline">Pendiente activación</span>
                    @elseif ($user->is_active)
                        <span class="badge badge-success badge-outline">Activo</span>
                    @else
                        <span class="badge badge-neutral badge-outline">Inactivo</span>
                    @endif
                    <span class="badge badge-neutral badge-outline">{{ $user->role?->label() }}</span>
                </div>
            </div>

            <div class="divider"></div>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase text-base-content/50">RUT</dt>
                    <dd class="mt-1 font-mono">{{ $user->rut ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase text-base-content/50">Activación</dt>
                    <dd class="mt-1">
                        @if ($user->activated_at)
                            {{ $user->activated_at->timezone('America/Santiago')->format('d/m/Y H:i') }}
                        @else
                            Pendiente — código enviado por correo
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase text-base-content/50">Registrado</dt>
                    <dd class="mt-1">{{ $user->created_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase text-base-content/50">Última actualización</dt>
                    <dd class="mt-1">{{ $user->updated_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</dd>
                </div>
                @if ($user->trashed())
                    <div>
                        <dt class="text-xs font-semibold uppercase text-base-content/50">Eliminado el</dt>
                        <dd class="mt-1">{{ $user->deleted_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </x-ui.card>

        <x-ui.card title="Acciones">
            <div class="space-y-2">
                @if ($user->trashed())
                    <form method="POST" action="{{ route('users.restore', $user->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm w-full">Restaurar usuario</button>
                    </form>
                @else
                    @can('resendActivation', $user)
                        <form method="POST" action="{{ route('users.resend-activation', $user) }}">
                            @csrf
                            <button type="submit" class="btn btn-info btn-outline btn-sm w-full">Reenviar código OTP</button>
                        </form>
                    @endcan
                    @can('toggleActive', $user)
                        <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline btn-sm w-full">
                                {{ $user->is_active ? 'Desactivar acceso' : 'Reactivar acceso' }}
                            </button>
                        </form>
                    @endcan
                    @can('delete', $user)
                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                              onsubmit="return confirm('¿Confirma dar de baja a este usuario?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-error btn-outline btn-sm w-full">Dar de baja</button>
                        </form>
                    @endcan
                @endif
            </div>
        </x-ui.card>
    </div>

    <x-access.access-audit-table
        :access-logs="$accessLogs"
        :export-excel-route="route('users.access-log.export', ['user' => $user, 'format' => 'csv'])"
        :export-pdf-route="route('users.access-log.export', ['user' => $user, 'format' => 'pdf'])"
    />

    <x-ui.card title="Historial de auditoría">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Fecha</th>
                        <th>Acción</th>
                        <th>Realizado por</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($auditLogs as $log)
                        <tr>
                            <td>{{ $log->created_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge-neutral badge-outline badge-sm">{{ $log->action->label() }}</span></td>
                            <td>{{ $log->user?->display_name ?? 'Sistema' }}</td>
                            <td class="font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-base-content/50">Sin registros</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
@endsection
