@extends('layouts.app')

@section('title', 'Movimientos')
@section('page-title', 'Historial de movimientos')
@section('page-subtitle', 'Kardex global · trazabilidad de entradas y salidas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm">← Volver al inventario</a>
        @include('inventory.partials.action-bar')
    </div>

    <div class="filter-toolbar">
        <form method="GET" class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
            <x-ui.field label="Buscar fármaco" for="search" class="xl:col-span-2">
                <x-ui.input id="search" type="search" name="search" value="{{ $filters['search'] }}" placeholder="Nombre o código" />
            </x-ui.field>
            <x-ui.field label="Tipo" for="movement_type">
                <x-ui.select id="movement_type" name="movement_type">
                    <option value="">Todos</option>
                    @foreach ($movementTypes as $type)
                        <option value="{{ $type->value }}" @selected($filters['movement_type'] === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="Bodega" for="pharmacy_id">
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
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary btn-sm w-full">Filtrar</button>
            </div>
        </form>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Fármaco</th>
                        <th>Lote</th>
                        <th>Cant.</th>
                        <th>Bodega</th>
                        <th>Profesional</th>
                        <th>Residente / Receta</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="whitespace-nowrap text-sm">{{ $movement->movement_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge-neutral badge-outline badge-sm">{{ $movement->movement_type->label() }}</span></td>
                            <td class="font-medium">{{ $movement->drug?->name }}</td>
                            <td class="font-mono text-xs">{{ $movement->batch?->batch_number }}</td>
                            <td>{{ $movement->quantity }}</td>
                            <td class="text-sm">
                                {{ $movement->pharmacy?->name }}
                                @if ($movement->destinationPharmacy)
                                    <span class="text-base-content/50">→ {{ $movement->destinationPharmacy->name }}</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ $movement->user?->display_name }}</td>
                            <td class="text-sm">
                                @if ($movement->resident)
                                    <a href="{{ route('residents.show', $movement->resident) }}" class="link link-primary">{{ $movement->resident->full_name }}</a>
                                @endif
                                @if ($movement->prescription_id)
                                    <p class="font-mono text-xs text-base-content/60">Rx {{ $movement->prescription_id }}</p>
                                @elseif (! $movement->resident)
                                    —
                                @endif
                            </td>
                            <td class="text-sm">${{ number_format($movement->total_value, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-8 text-center text-base-content/50">Sin movimientos registrados</td></tr>
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
