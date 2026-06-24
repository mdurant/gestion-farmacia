@extends('layouts.app')

@section('title', 'Residentes')
@section('page-title', 'Residentes')
@section('page-subtitle', 'Fichas clínicas y trazabilidad de medicación')

@section('content')
<div class="space-y-6">
    @include('residents.partials.action-bar')

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Total residentes" :value="$stats['total']" color="primary" icon="users" />
        <x-ui.stat-card label="Activos" :value="$stats['active']" color="success" icon="activity" />
        <x-ui.stat-card label="Con medicación registrada" :value="$stats['with_medications']" color="info" icon="box" />
    </div>

    <div class="filter-toolbar">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <x-ui.field label="Buscar" for="search" class="md:col-span-2"
                        hint="Búsqueda en memoria sobre datos descifrados (nombre, RUT, habitación).">
                <x-ui.input id="search" type="search" name="search" value="{{ $filters['search'] }}"
                            placeholder="Nombre, RUT o habitación" />
            </x-ui.field>
            <x-ui.field label="Centro de costo" for="cost_center_id">
                <x-ui.select id="cost_center_id" name="cost_center_id">
                    <option value="">Todos</option>
                    @foreach ($costCenters as $center)
                        <option value="{{ $center->id }}" @selected($filters['cost_center_id'] == $center->id)>{{ $center->name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="Estado" for="is_active">
                <x-ui.select id="is_active" name="is_active">
                    <option value="">Todos</option>
                    <option value="1" @selected($filters['is_active'] === true)>Activos</option>
                    <option value="0" @selected($filters['is_active'] === false)>Inactivos</option>
                </x-ui.select>
            </x-ui.field>
            <div class="flex items-end gap-2 md:col-span-4">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="{{ route('residents.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-base-content/60">Exportar listado con los filtros aplicados (datos descifrados)</p>
        <x-ui.export-buttons
            :excel-route="route('residents.export', ['format' => 'csv'] + request()->query())"
            :pdf-route="route('residents.export', ['format' => 'pdf'] + request()->query())"
        />
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Residente</th>
                        <th>RUT</th>
                        <th>Habitación</th>
                        <th>Centro de costo</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($residents as $resident)
                        <tr>
                            <td class="font-medium">{{ $resident->full_name }}</td>
                            <td class="font-mono text-sm">{{ $resident->rut }}</td>
                            <td>{{ $resident->room_number ?? '—' }}</td>
                            <td>{{ $resident->costCenter?->name ?? '—' }}</td>
                            <td>
                                @if ($resident->is_active)
                                    <span class="badge badge-success badge-outline badge-sm">Activo</span>
                                @else
                                    <span class="badge badge-warning badge-outline badge-sm">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('residents.show', $resident) }}" class="btn btn-xs btn-primary btn-outline">Ver ficha</a>
                                @can('update', $resident)
                                    <a href="{{ route('residents.edit', $resident) }}" class="btn btn-xs">Editar</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-base-content/50">No hay residentes registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($residents->hasPages())
            <div class="border-t border-base-300 px-6 py-4">{{ $residents->links() }}</div>
        @endif
    </x-ui.card>
</div>
@endsection
