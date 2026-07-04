@extends('layouts.app')

@section('title', 'Catálogo de fármacos')
@section('page-title', 'Catálogo de fármacos')
@section('page-subtitle', 'Códigos, stock mínimo y fármacos controlados')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm">← Inventario</a>
        @can('create', \App\Models\Drug::class)
            <a href="{{ route('inventory.drugs.create') }}" class="btn btn-primary btn-sm gap-2 shadow-sm">
                <x-ui.icon name="plus" class="size-4" />
                Nuevo fármaco
            </a>
        @endcan
    </div>

    <div class="filter-toolbar">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <x-ui.field label="Buscar" for="search" class="min-w-[220px] flex-1">
                <x-ui.input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Nombre o código" />
            </x-ui.field>
            <label class="fieldset-label cursor-pointer gap-2 rounded-lg border border-base-300 px-3 py-2.5">
                <input type="checkbox" name="controlled" value="1" class="checkbox checkbox-sm" @checked(request('controlled')) />
                <span>Solo controlados</span>
            </label>
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-ui.records-found :items="$drugs" class="justify-start" />
        <div class="flex flex-wrap items-center gap-3">
            <p class="text-sm text-base-content/60">Exportar listado con los filtros aplicados</p>
            <x-ui.export-buttons
                :excel-route="route('inventory.drugs.export', ['format' => 'csv'] + request()->query())"
                :pdf-route="route('inventory.drugs.export', ['format' => 'pdf'] + request()->query())"
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
                        <th>Categoría</th>
                        <th>Stock mín.</th>
                        <th>Costo ref.</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($drugs as $drug)
                        <tr>
                            <td class="font-mono text-sm">{{ $drug->code }}</td>
                            <td class="font-medium">
                                {{ $drug->name }}
                                @if ($drug->is_controlled || $drug->is_narcotic)
                                    <span class="badge badge-error badge-outline badge-xs ms-1">Controlado</span>
                                @endif
                            </td>
                            <td>{{ $drug->category ?? '—' }}</td>
                            <td>{{ $drug->min_stock }}</td>
                            <td>${{ number_format($drug->unit_cost, 0, ',', '.') }}</td>
                            <td>
                                @if ($drug->is_active)
                                    <span class="badge badge-success badge-outline badge-sm">Activo</span>
                                @else
                                    <span class="badge badge-warning badge-outline badge-sm">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('inventory.drugs.show', $drug) }}" class="btn btn-xs btn-primary btn-outline">Kardex</a>
                                @can('update', $drug)
                                    <a href="{{ route('inventory.drugs.edit', $drug) }}" class="btn btn-xs">Editar</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-base-content/50">No hay fármacos registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($drugs->hasPages())
            <div class="border-t border-base-300 px-6 py-4">{{ $drugs->links() }}</div>
        @endif
    </x-ui.card>
</div>
@endsection
