@extends('layouts.app')

@section('title', $pharmacy->name)
@section('page-title', $pharmacy->name)
@section('page-subtitle', $pharmacy->code . ' · ' . $pharmacy->type->label())

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('pharmacies.index') }}" class="btn btn-ghost btn-sm">← Bodegas</a>
        @can('update', $pharmacy)
            <a href="{{ route('pharmacies.edit', $pharmacy) }}" class="btn btn-sm btn-outline">Editar</a>
        @endcan
        @can('create', \App\Models\InventoryMovement::class)
            <a href="{{ route('inventory.movements.transfer.create') }}" class="btn btn-sm btn-primary btn-outline">Nuevo traslado</a>
        @endcan
        <a href="{{ route('inventory.index', ['pharmacy_id' => $pharmacy->id]) }}" class="btn btn-sm btn-ghost ms-auto">Ver stock</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Unidades en stock" :value="$totalStock" color="primary" icon="box" />
        <x-ui.stat-card label="Lotes activos" :value="$pharmacy->batches->where('quantity', '>', 0)->count()" color="info" icon="activity" />
        <x-ui.stat-card label="Estado" :value="$pharmacy->is_active ? 'Activa' : 'Inactiva'" :color="$pharmacy->is_active ? 'success' : 'warning'" icon="chart" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Datos generales">
            <dl class="grid gap-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Código</dt>
                    <dd class="font-mono font-medium">{{ $pharmacy->code }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Tipo</dt>
                    <dd><span class="badge badge-neutral badge-outline badge-sm">{{ $pharmacy->type->label() }}</span></dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Centro de costo</dt>
                    <dd>
                        @if ($pharmacy->costCenter)
                            <a href="{{ route('pharmacies.cost-centers.show', $pharmacy->costCenter) }}" class="link link-primary">
                                {{ $pharmacy->costCenter->name }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @if ($pharmacy->description)
                    <div>
                        <dt class="mb-1 text-base-content/60">Descripción</dt>
                        <dd>{{ $pharmacy->description }}</dd>
                    </div>
                @endif
            </dl>
        </x-ui.card>

        <x-ui.card title="Stock por lote">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="text-base-content/60">
                            <th>Fármaco</th>
                            <th>Lote</th>
                            <th>Cant.</th>
                            <th>Vence</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pharmacy->batches->where('quantity', '>', 0) as $batch)
                            <tr>
                                <td class="text-sm">{{ $batch->drug?->name }}</td>
                                <td class="font-mono text-xs">{{ $batch->batch_number }}</td>
                                <td>{{ $batch->quantity }}</td>
                                <td class="text-sm">{{ $batch->expiration_date?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-base-content/50">Sin stock registrado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card title="Traslados recientes">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Fecha</th>
                        <th>Fármaco</th>
                        <th>Cant.</th>
                        <th>Origen / Destino</th>
                        <th>Profesional</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTransfers as $movement)
                        <tr>
                            <td class="whitespace-nowrap text-sm">{{ $movement->movement_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</td>
                            <td class="font-medium">{{ $movement->drug?->name }}</td>
                            <td>{{ $movement->quantity }}</td>
                            <td class="text-sm">
                                {{ $movement->pharmacy?->name }}
                                @if ($movement->destinationPharmacy)
                                    <span class="text-base-content/50">→ {{ $movement->destinationPharmacy->name }}</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ $movement->user?->display_name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-base-content/50">Sin traslados registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-base-300 px-6 py-3 text-right">
            <a href="{{ route('pharmacies.transfers', ['pharmacy_id' => $pharmacy->id]) }}" class="btn btn-ghost btn-xs">Ver historial completo</a>
        </div>
    </x-ui.card>

    @can('delete', $pharmacy)
        <x-ui.card title="Zona de riesgo">
            <form method="POST" action="{{ route('pharmacies.destroy', $pharmacy) }}"
                  onsubmit="return confirm('¿Dar de baja esta bodega? Solo es posible si no tiene stock activo.');">
                @csrf
                @method('DELETE')
                @error('delete')
                    <div class="alert alert-error mb-4 text-sm">{{ $message }}</div>
                @enderror
                <button type="submit" class="btn btn-error btn-outline btn-sm">Dar de baja bodega</button>
            </form>
        </x-ui.card>
    @endcan
</div>
@endsection
