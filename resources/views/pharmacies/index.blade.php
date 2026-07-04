@extends('layouts.app')

@section('title', 'Bodegas')
@section('page-title', 'Bodegas y ubicaciones')
@section('page-subtitle', 'Central, botiquines y módulos de emergencia')

@section('content')
<div class="space-y-6">
    @include('pharmacies.partials.action-bar')

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Total bodegas" :value="$stats['total']" color="primary" icon="box" />
        <x-ui.stat-card label="Activas" :value="$stats['active']" color="success" icon="activity" />
        <x-ui.stat-card label="Con stock" :value="$stats['with_stock']" color="info" icon="chart" />
    </div>

    <div class="filter-toolbar">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <x-ui.field label="Buscar" for="search" class="md:col-span-2">
                <x-ui.input id="search" type="search" name="search" value="{{ $filters['search'] }}" placeholder="Nombre o código" />
            </x-ui.field>
            <x-ui.field label="Tipo" for="type">
                <x-ui.select id="type" name="type">
                    <option value="">Todos</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="Centro de costo" for="cost_center_id">
                <x-ui.select id="cost_center_id" name="cost_center_id">
                    <option value="">Todos</option>
                    @foreach ($costCenters as $center)
                        <option value="{{ $center->id }}" @selected($filters['cost_center_id'] == $center->id)>{{ $center->name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <div class="flex items-end gap-2 md:col-span-4">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="{{ route('pharmacies.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-ui.records-found :items="$pharmacies" class="justify-start" />
        <div class="flex flex-wrap items-center gap-3">
            <p class="text-sm text-base-content/60">Exportar listado con los filtros aplicados</p>
            <x-ui.export-buttons
                :excel-route="route('pharmacies.export', ['format' => 'csv'] + request()->query())"
                :pdf-route="route('pharmacies.export', ['format' => 'pdf'] + request()->query())"
            />
        </div>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Centro de costo</th>
                        <th>Lotes c/stock</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pharmacies as $pharmacy)
                        <tr>
                            <td class="font-mono text-sm">{{ $pharmacy->code }}</td>
                            <td class="font-medium">{{ $pharmacy->name }}</td>
                            <td><span class="badge badge-neutral badge-outline badge-sm">{{ $pharmacy->type->label() }}</span></td>
                            <td>
                                @if ($pharmacy->costCenter)
                                    <a href="{{ route('pharmacies.cost-centers.show', $pharmacy->costCenter) }}" class="link link-primary text-sm">
                                        {{ $pharmacy->costCenter->name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $pharmacy->batches_in_stock_count }}</td>
                            <td>
                                @if ($pharmacy->is_active)
                                    <span class="badge badge-success badge-outline badge-sm">Activa</span>
                                @else
                                    <span class="badge badge-warning badge-outline badge-sm">Inactiva</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('pharmacies.show', $pharmacy) }}" class="btn btn-xs btn-primary btn-outline">Ver</a>
                                @can('update', $pharmacy)
                                    <a href="{{ route('pharmacies.edit', $pharmacy) }}" class="btn btn-xs">Editar</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-base-content/50">No hay bodegas registradas</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pharmacies->hasPages())
            <div class="border-t border-base-300 px-6 py-4">{{ $pharmacies->links() }}</div>
        @endif
    </x-ui.card>
</div>
@endsection
