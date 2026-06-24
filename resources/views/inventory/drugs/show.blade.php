@extends('layouts.app')

@section('title', $drug->name)
@section('page-title', $drug->name)
@section('page-subtitle', 'Kardex · ' . $drug->code)

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('inventory.drugs.index') }}" class="btn btn-ghost btn-sm">← Catálogo</a>
        <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm">Inventario</a>
        @can('update', $drug)
            <a href="{{ route('inventory.drugs.edit', $drug) }}" class="btn btn-primary btn-sm">Editar fármaco</a>
        @endcan
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Stock total" :value="$totalStock" color="primary" icon="box" />
        <x-ui.stat-card label="Stock mínimo" :value="$drug->min_stock" color="warning" icon="alert" />
        <x-ui.stat-card label="Costo referencial" :value="'$'.number_format($drug->unit_cost, 0, ',', '.')" color="info" icon="chart" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Ficha del fármaco" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-base-content/50">Código</dt><dd class="font-mono font-medium">{{ $drug->code }}</dd></div>
                <div><dt class="text-base-content/50">Categoría</dt><dd>{{ $drug->category ?? '—' }}</dd></div>
                <div><dt class="text-base-content/50">Presentación</dt><dd>{{ $drug->presentation ?? '—' }}</dd></div>
                <div><dt class="text-base-content/50">Principio activo</dt><dd>{{ $drug->active_ingredient ?? '—' }}</dd></div>
                <div>
                    <dt class="text-base-content/50">Clasificación</dt>
                    <dd class="flex flex-wrap gap-1 mt-1">
                        @if ($drug->is_controlled)<span class="badge badge-error badge-outline badge-sm">Controlado</span>@endif
                        @if ($drug->is_narcotic)<span class="badge badge-error badge-sm">Estupefaciente</span>@endif
                        @if (! $drug->is_controlled && ! $drug->is_narcotic)<span class="badge badge-neutral badge-outline badge-sm">General</span>@endif
                    </dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Stock por bodega" class="lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead><tr class="text-base-content/60"><th>Bodega</th><th>Lote</th><th>Vence</th><th>Cantidad</th></tr></thead>
                    <tbody>
                        @forelse ($drug->batches as $batch)
                            <tr>
                                <td>{{ $batch->pharmacy?->name }}</td>
                                <td class="font-mono text-xs">{{ $batch->batch_number }}</td>
                                <td>{{ $batch->expiration_date->format('d/m/Y') }}</td>
                                <td class="font-semibold">{{ $batch->availableQuantity() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-base-content/50">Sin lotes registrados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card title="Kardex — movimientos">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Lote</th>
                        <th>Cant.</th>
                        <th>Bodega</th>
                        <th>Profesional</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td>{{ $movement->movement_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge-neutral badge-outline badge-xs">{{ $movement->movement_type->label() }}</span></td>
                            <td class="font-mono text-xs">{{ $movement->batch?->batch_number }}</td>
                            <td>{{ $movement->quantity }}</td>
                            <td>{{ $movement->pharmacy?->name }}</td>
                            <td>{{ $movement->user?->display_name }}</td>
                            <td class="max-w-xs truncate text-sm">{{ $movement->reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-base-content/50">Sin movimientos para este fármaco</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($movements->hasPages())
            <div class="border-t border-base-300 px-6 py-4">{{ $movements->links() }}</div>
        @endif
    </x-ui.card>
</div>
@endsection
