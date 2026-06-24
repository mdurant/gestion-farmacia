@extends('layouts.app')

@section('title', 'Roles y permisos')
@section('page-title', 'Roles y permisos')
@section('page-subtitle', 'Matriz de accesos del sistema')

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach (\App\Enums\UserRole::cases() as $roleCase)
            <x-ui.stat-card
                :label="$roleCase->label()"
                :value="$userCounts[$roleCase->value] ?? 0"
                color="primary"
                icon="users"
            />
        @endforeach
    </div>

    <x-ui.card title="Matriz de permisos">
        <p class="mb-4 text-sm text-base-content/65">
            Cada celda indica si el rol incluye ese permiso. Los accesos se asignan automáticamente al seleccionar el rol de un usuario.
        </p>

        <div class="vx-permission-legend mb-5 flex flex-wrap gap-3">
            <span class="vx-permission-legend__item">
                <x-ui.permission-cell :granted="true" />
                <span>Incluido en el rol</span>
            </span>
            <span class="vx-permission-legend__item">
                <x-ui.permission-cell :granted="false" />
                <span>No incluido</span>
            </span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-base-300">
            <table class="vx-permission-matrix table table-sm">
                <thead>
                    <tr>
                        <th class="vx-permission-matrix__permission-col">Permiso</th>
                        @foreach ($roles as $role)
                            @php($roleCase = \App\Enums\UserRole::from($role->name))
                            <th class="vx-permission-matrix__role-col text-center">
                                <span class="vx-permission-matrix__role-name">{{ $roleCase->label() }}</span>
                                <span class="vx-permission-matrix__role-count">
                                    {{ $userCounts[$role->name] ?? 0 }} {{ ($userCounts[$role->name] ?? 0) === 1 ? 'usuario' : 'usuarios' }}
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissionsByGroup as $groupKey => $permissions)
                        <tr class="vx-permission-matrix__group-row">
                            <td colspan="{{ $roles->count() + 1 }}">
                                {{ $permissions->first()->groupLabel() }}
                            </td>
                        </tr>
                        @foreach ($permissions as $permission)
                            <tr class="vx-permission-matrix__data-row">
                                <td class="vx-permission-matrix__permission-col">
                                    <p class="font-medium">{{ $permission->label() }}</p>
                                    <p class="mt-0.5 text-xs leading-snug text-base-content/55">{{ $permission->description() }}</p>
                                </td>
                                @foreach ($roles as $role)
                                    <td class="vx-permission-matrix__role-col text-center">
                                        <x-ui.permission-cell :granted="$role->hasPermissionTo($permission->value)" />
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="alert alert-info shadow-sm">
        <span>Para cambiar los permisos de una persona, edita su usuario y selecciona otro rol. La matriz es de solo lectura.</span>
    </div>
</div>
@endsection
