@extends('layouts.app')

@section('title', 'Lote ' . $batch->batch_number)
@section('page-title', 'Lote ' . $batch->batch_number)
@section('page-subtitle', $batch->drug?->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm">← Inventario</a>
        @if ($batch->drug)
            <a href="{{ route('inventory.drugs.show', $batch->drug) }}" class="btn btn-ghost btn-sm">Kardex fármaco</a>
        @endif
        @can('update', $batch)
            <a href="{{ route('inventory.batches.edit', $batch) }}" class="btn btn-primary btn-sm">Editar lote</a>
        @endcan
        @can('create', \App\Models\InventoryMovement::class)
            @if ($batch->availableQuantity() > 0)
                <a href="{{ route('inventory.movements.transfer.create') }}" class="btn btn-sm btn-outline">Trasladar</a>
            @endif
        @endcan
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Disponible" :value="$batch->availableQuantity()" color="primary" icon="box" />
        <x-ui.stat-card label="Vencimiento" :value="$batch->expiration_date->format('d/m/Y')" :color="$batch->isExpiringWithinDays(30) ? 'warning' : 'success'" icon="alert" />
        <x-ui.stat-card label="Costo unitario" :value="'$'.number_format($batch->unit_cost, 0, ',', '.')" color="info" icon="chart" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Datos del lote">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-base-content/50">Fármaco</dt><dd class="font-medium">{{ $batch->drug?->name }}</dd></div>
                <div><dt class="text-base-content/50">Código fármaco</dt><dd class="font-mono">{{ $batch->drug?->code }}</dd></div>
                <div><dt class="text-base-content/50">Bodega</dt><dd>{{ $batch->pharmacy?->name }}</dd></div>
                <div><dt class="text-base-content/50">Proveedor</dt><dd>{{ $batch->supplier_name ?? '—' }}</dd></div>
                <div><dt class="text-base-content/50">Documento</dt><dd>{{ $batch->supplier_document ?? '—' }}</dd></div>
                <div><dt class="text-base-content/50">Recepción</dt><dd>{{ $batch->received_at?->timezone('America/Santiago')->format('d/m/Y') ?? '—' }}</dd></div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Estado">
            <div class="flex flex-wrap gap-2">
                @if ($batch->drug && \App\Models\Batch::query()->where('drug_id', $batch->drug_id)->sum('quantity') <= $batch->drug->min_stock)
                    <span class="badge badge-error badge-outline">Stock crítico</span>
                @endif
                @if ($batch->isExpiringWithinDays(30) && $batch->quantity > 0)
                    <span class="badge badge-warning badge-outline">Por vencer</span>
                @endif
                @if ($batch->drug?->is_controlled || $batch->drug?->is_narcotic)
                    <span class="badge badge-error">Controlado</span>
                @endif
                @if ($batch->availableQuantity() === 0)
                    <span class="badge badge-neutral badge-outline">Sin stock</span>
                @endif
            </div>
        </x-ui.card>
    </div>

    <x-ui.card title="Movimientos del lote">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Fecha</th>
                        <th>Tipo</th>
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
                            <td>{{ $movement->quantity }}</td>
                            <td>
                                {{ $movement->pharmacy?->name }}
                                @if ($movement->destinationPharmacy)
                                    <span class="text-base-content/50">→ {{ $movement->destinationPharmacy->name }}</span>
                                @endif
                            </td>
                            <td>{{ $movement->user?->display_name }}</td>
                            <td class="max-w-xs truncate">{{ $movement->reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-base-content/50">Sin movimientos para este lote</td></tr>
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
