@extends('layouts.app')

@section('title', 'Usuarios')
@section('page-title', 'Gestión de usuarios')
@section('page-subtitle', 'Personal clínico y administrativo')

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Total listado" :value="$stats['total']" color="primary" icon="users" />
        <x-ui.stat-card label="Activos" :value="$stats['active']" color="success" icon="activity" />
        <x-ui.stat-card label="Inactivos" :value="$stats['inactive']" color="warning" icon="alert" />
    </div>

    <div class="filter-toolbar">
        <form method="GET" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.field label="Buscar" for="search" class="lg:col-span-2">
                    <x-ui.input id="search" type="search" name="search" value="{{ request('search') }}"
                                placeholder="Nombre o correo electrónico" />
                </x-ui.field>

                <x-ui.field label="Rol" for="role">
                    <x-ui.select id="role" name="role">
                        <option value="">Todos los roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Estado" for="is_active">
                    <x-ui.select id="is_active" name="is_active">
                        <option value="">Todos los estados</option>
                        <option value="1" @selected(request('is_active') === '1')>Activos</option>
                        <option value="0" @selected(request('is_active') === '0')>Inactivos</option>
                    </x-ui.select>
                </x-ui.field>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label class="fieldset-label cursor-pointer gap-3 rounded-lg border border-base-300 bg-base-200/50 px-3 py-2.5">
                    <input type="checkbox" name="trashed" value="1" class="toggle toggle-sm toggle-warning"
                           @checked(request('trashed')) />
                    <span>Incluir usuarios eliminados</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    <button type="submit" class="btn btn-primary btn-sm">Aplicar filtros</button>
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">+ Nuevo usuario</a>
                </div>
            </div>
        </form>
    </div>

    <x-ui.records-found :items="$users" />

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>RUT</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr @class(['opacity-60' => ! $user->is_active || $user->trashed()])>
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :name="$user->display_name" size="sm" ring />
                                    <span class="font-medium">{{ $user->display_name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td class="font-mono text-sm">{{ $user->rut ?? '—' }}</td>
                            <td><span class="badge badge-neutral badge-outline">{{ $user->role?->label() }}</span></td>
                            <td>
                                @if ($user->trashed())
                                    <span class="badge badge-error badge-outline">Eliminado</span>
                                @elseif ($user->isPendingActivation())
                                    <span class="badge badge-warning badge-outline">Pendiente activación</span>
                                @elseif ($user->is_active)
                                    <span class="badge badge-success badge-outline">Activo</span>
                                @else
                                    <span class="badge badge-neutral badge-outline">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions">
                                    @if ($user->trashed())
                                        <form method="POST" action="{{ route('users.restore', $user->id) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success">Restaurar</button>
                                        </form>
                                    @else
                                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm">Ver ficha</a>
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary btn-outline">Editar</a>
                                        @if (auth()->id() !== $user->id && ! $user->isPendingActivation())
                                            <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        @class([
                                                            'btn btn-sm',
                                                            'btn-warning' => $user->is_active,
                                                            'btn-success btn-outline' => ! $user->is_active,
                                                        ])>
                                                    {{ $user->is_active ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-base-content/50">
                                No hay usuarios que coincidan con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-base-300 px-6 py-4">{{ $users->links() }}</div>
        @endif
    </x-ui.card>
</div>
@endsection
