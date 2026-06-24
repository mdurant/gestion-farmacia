@extends('layouts.app')

@section('title', 'Inventario')
@section('page-title', 'Inventario de fármacos')
@section('page-subtitle', 'Stock por lote, kardex y movimientos')

@section('content')
<div class="space-y-6">
    @include('inventory.partials.action-bar')

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Fármacos activos" :value="$stats['drugs']" color="primary" icon="box" />
        <x-ui.stat-card label="Lotes con stock" :value="$stats['batches']" color="success" icon="activity" />
        <x-ui.stat-card label="Stock crítico" :value="$stats['low_stock']" color="error" icon="alert" />
        <x-ui.stat-card label="Por vencer (30 días)" :value="$stats['expiring']" color="warning" icon="alert" />
    </div>

    @if ($alerts->isNotEmpty())
        <x-ui.card title="Alertas activas">
            <div class="space-y-3">
                @foreach ($alerts as $alert)
                    <div @class([
                        'vx-inventory-alert flex flex-wrap items-start justify-between gap-3 rounded-xl border-2 px-4 py-3.5 text-sm shadow-sm',
                        'vx-inventory-alert--critical' => $alert->severity === 'error',
                        'vx-inventory-alert--warning' => $alert->severity === 'warning',
                        'vx-inventory-alert--info' => ! in_array($alert->severity, ['error', 'warning']),
                    ])>
                        <div>
                            <p class="font-bold">{{ $alert->title }}</p>
                            <p class="mt-0.5 opacity-85">{{ $alert->message }}</p>
                        </div>
                        @if ($alert->batch_id)
                            <a href="{{ route('inventory.batches.show', $alert->batch_id) }}" class="btn btn-xs btn-outline bg-base-100/80">Ver lote</a>
                        @elseif ($alert->drug_id)
                            <a href="{{ route('inventory.drugs.show', $alert->drug_id) }}" class="btn btn-xs btn-outline bg-base-100/80">Ver kardex</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @endif

    <div class="filter-toolbar">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <x-ui.field label="Buscar" for="search" class="md:col-span-2">
                <x-ui.input id="search" type="search" name="search" value="{{ $filters['search'] }}"
                            placeholder="Fármaco, código o lote" />
            </x-ui.field>
            <x-ui.field label="Bodega" for="pharmacy_id">
                <x-ui.select id="pharmacy_id" name="pharmacy_id">
                    <option value="">Todas</option>
                    @foreach ($pharmacies as $pharmacy)
                        <option value="{{ $pharmacy->id }}" @selected($filters['pharmacy_id'] == $pharmacy->id)>{{ $pharmacy->name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="Estado" for="status">
                <x-ui.select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="in_stock" @selected($filters['status'] === 'in_stock')>Con stock</option>
                    <option value="low_stock" @selected($filters['status'] === 'low_stock')>Stock crítico</option>
                    <option value="expiring" @selected($filters['status'] === 'expiring')>Por vencer</option>
                </x-ui.select>
            </x-ui.field>
            <div class="flex items-end gap-2 md:col-span-4">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                <a href="{{ route('inventory.drugs.index') }}" class="btn btn-ghost btn-sm ms-auto">Ver catálogo de fármacos</a>
            </div>
        </form>
    </div>

    <x-ui.card title="Stock por lote">
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Fármaco</th>
                        <th>Lote</th>
                        <th>Bodega</th>
                        <th>Vencimiento</th>
                        <th>Disponible</th>
                        <th>Costo unit.</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        @php
                            $drugTotal = $batch->drug ? \App\Models\Batch::query()->where('drug_id', $batch->drug_id)->sum('quantity') : 0;
                            $isLow = $batch->drug && $drugTotal <= $batch->drug->min_stock;
                            $isExpiring = $batch->isExpiringWithinDays(30);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('inventory.drugs.show', $batch->drug) }}" class="font-medium link link-primary">
                                    {{ $batch->drug?->name }}
                                </a>
                                <p class="text-xs text-base-content/50">{{ $batch->drug?->code }}</p>
                                @if ($batch->drug?->is_controlled || $batch->drug?->is_narcotic)
                                    <span class="badge badge-error badge-outline badge-xs mt-1">Controlado</span>
                                @endif
                            </td>
                            <td class="font-mono text-sm">{{ $batch->batch_number }}</td>
                            <td>{{ $batch->pharmacy?->name }}</td>
                            <td>
                                <span @class(['text-error font-medium' => $isExpiring])>
                                    {{ $batch->expiration_date->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                <span @class(['font-semibold text-error' => $isLow])>{{ $batch->availableQuantity() }}</span>
                                @if ($isLow)
                                    <span class="badge badge-error badge-outline badge-xs ms-1">Crítico</span>
                                @endif
                            </td>
                            <td>${{ number_format($batch->unit_cost, 0, ',', '.') }}</td>
                            <td class="text-right">
                                <a href="{{ route('inventory.batches.show', $batch) }}" class="btn btn-xs btn-primary btn-outline">Lote</a>
                                <a href="{{ route('inventory.drugs.show', $batch->drug) }}" class="btn btn-xs">Kardex</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-base-content/50">
                                No hay lotes que coincidan con los filtros.
                                @can('create', \App\Models\InventoryMovement::class)
                                    <a href="{{ route('inventory.movements.entry.create') }}" class="link link-primary">Registrar entrada</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($batches->hasPages())
            <div class="border-t border-base-300 px-6 py-4">{{ $batches->links() }}</div>
        @endif
    </x-ui.card>
</div>
@endsection
