@extends('layouts.app')

@section('title', 'Centros de costo')
@section('page-title', 'Centros de costo')
@section('page-subtitle', 'Pisos, pabellones y ubicaciones clínicas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('pharmacies.index') }}" class="btn btn-ghost btn-sm">← Bodegas</a>
        @can('create', \App\Models\CostCenter::class)
            <a href="{{ route('pharmacies.cost-centers.create') }}" class="btn btn-primary btn-sm">+ Nuevo centro</a>
        @endcan
    </div>

    <div class="filter-toolbar">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <x-ui.field label="Buscar" for="search" class="min-w-64">
                <x-ui.input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Nombre o código" />
            </x-ui.field>
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            <a href="{{ route('pharmacies.cost-centers.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
        </form>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Piso</th>
                        <th>Pabellón</th>
                        <th>Bodegas</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($costCenters as $center)
                        <tr>
                            <td class="font-mono text-sm">{{ $center->code }}</td>
                            <td class="font-medium">{{ $center->name }}</td>
                            <td>{{ $center->floor ?? '—' }}</td>
                            <td>{{ $center->pavilion ?? '—' }}</td>
                            <td>{{ $center->pharmacies_count }}</td>
                            <td>
                                @if ($center->is_active)
                                    <span class="badge badge-success badge-outline badge-sm">Activo</span>
                                @else
                                    <span class="badge badge-warning badge-outline badge-sm">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('pharmacies.cost-centers.show', $center) }}" class="btn btn-xs btn-primary btn-outline">Ver</a>
                                @can('update', $center)
                                    <a href="{{ route('pharmacies.cost-centers.edit', $center) }}" class="btn btn-xs">Editar</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-base-content/50">No hay centros de costo registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($costCenters->hasPages())
            <div class="border-t border-base-300 px-6 py-4">{{ $costCenters->links() }}</div>
        @endif
    </x-ui.card>
</div>
@endsection
