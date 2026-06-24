@extends('layouts.app')

@section('title', 'Editar usuario')
@section('page-title', $user->display_name)
@section('page-subtitle', 'Actualizar datos, rol y estado de acceso')

@section('content')
<div class="mb-4">
    <a href="{{ route('users.show', $user) }}" class="btn btn-ghost btn-sm">← Ver ficha</a>
</div>

<div class="grid gap-6 xl:grid-cols-3">
    <x-ui.card class="xl:col-span-2">
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('users.partials.form', ['user' => $user, 'roles' => $roles])
            <div class="form-actions border-t border-base-300 pt-4">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Volver</a>
                @can('delete', $user)
                    <button type="button" class="btn btn-error btn-outline ms-auto"
                            onclick="document.getElementById('delete-user-form').submit()">
                        Dar de baja
                    </button>
                @endcan
            </div>
        </form>
        @can('delete', $user)
            <form id="delete-user-form" method="POST" action="{{ route('users.destroy', $user) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endcan
    </x-ui.card>

    <x-ui.card title="Auditoría reciente">
        <div class="space-y-3">
            @forelse ($auditLogs as $log)
                <div class="rounded-lg border border-base-300 bg-base-200/50 p-3 text-sm">
                    <p class="font-medium">{{ $log->action->label() }}</p>
                    <p class="text-base-content/55">{{ $log->created_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</p>
                </div>
            @empty
                <p class="text-sm text-base-content/50">Sin registros de auditoría.</p>
            @endforelse
        </div>
    </x-ui.card>
</div>
@endsection
