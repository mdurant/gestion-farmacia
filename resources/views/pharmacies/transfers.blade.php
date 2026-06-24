@extends('layouts.app')

@section('title', 'Historial de traslados')
@section('page-title', 'Historial de traslados')
@section('page-subtitle', 'Movimientos entre bodegas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('pharmacies.index') }}" class="btn btn-ghost btn-sm">← Bodegas</a>
        @can('create', \App\Models\InventoryMovement::class)
            <a href="{{ route('inventory.movements.transfer.create') }}" class="btn btn-primary btn-sm">+ Nuevo traslado</a>
        @endcan
    </div>

    <div class="filter-toolbar">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <x-ui.field label="Bodega (origen o destino)" for="pharmacy_id" class="md:col-span-2">
                <x-ui.select id="pharmacy_id" name="pharmacy_id">
                    <option value="">Todas</option>
                    @foreach ($pharmacies as $pharmacy)
                        <option value="{{ $pharmacy->id }}" @selected($filters['pharmacy_id'] == $pharmacy->id)>{{ $pharmacy->name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="Desde" for="from">
                <x-ui.input id="from" type="date" name="from" value="{{ $filters['from'] }}" />
            </x-ui.field>
            <x-ui.field label="Hasta" for="to">
                <x-ui.input id="to" type="date" name="to" value="{{ $filters['to'] }}" />
            </x-ui.field>
            <div class="flex items-end gap-2 md:col-span-4">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="{{ route('pharmacies.transfers') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            </div>
        </form>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Fecha</th>
                        <th>Fármaco</th>
                        <th>Lote</th>
                        <th>Cant.</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Profesional</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="whitespace-nowrap text-sm">{{ $movement->movement_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</td>
                            <td class="font-medium">{{ $movement->drug?->name }}</td>
                            <td class="font-mono text-xs">{{ $movement->batch?->batch_number }}</td>
                            <td>{{ $movement->quantity }}</td>
                            <td class="text-sm">
                                @if ($movement->pharmacy)
                                    <a href="{{ route('pharmacies.show', $movement->pharmacy) }}" class="link link-primary">{{ $movement->pharmacy->name }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-sm">
                                @if ($movement->destinationPharmacy)
                                    <a href="{{ route('pharmacies.show', $movement->destinationPharmacy) }}" class="link link-primary">{{ $movement->destinationPharmacy->name }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-sm">{{ $movement->user?->display_name }}</td>
                            <td class="text-sm">${{ number_format($movement->total_value, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-base-content/50">Sin traslados registrados</td></tr>
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
