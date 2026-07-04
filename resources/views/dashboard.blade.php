@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Panel central')
@section('page-subtitle', 'Inventario, trazabilidad y alertas en tiempo real')

@section('content')
<div class="space-y-6">
    {{-- Banner de bienvenida estilo Vuesax --}}
    <div class="vx-welcome-banner">
        <div>
            <p class="text-sm font-medium text-white/80">Bienvenido, {{ auth()->user()->first_name ?? auth()->user()->display_name }}</p>
            <h2 class="mt-1 text-2xl font-bold">Gestión farmacéutica centralizada</h2>
            <p class="mt-2 max-w-xl text-sm text-white/85">
                Monitoree inventario, movimientos y alertas críticas de su residencia en un solo panel.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('inventory.index') }}" class="btn btn-sm">Ver inventario</a>
            <a href="{{ route('reports.index') }}" class="btn btn-sm">Reportes</a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Residentes activos" :value="$stats['residents']" color="primary" icon="users" />
        <x-ui.stat-card label="Movimientos hoy" :value="$stats['movements_today']" color="info" icon="activity" />
        <x-ui.stat-card label="Alertas pendientes" :value="$stats['critical_alerts']" color="warning" icon="alert" />
        <x-ui.stat-card label="Lotes activos" :value="$stats['active_batches']" color="success" icon="box" />
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <x-ui.card title="Movimientos recientes" class="xl:col-span-2">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="text-base-content/60">
                            <th>Fecha</th>
                            <th>Fármaco</th>
                            <th>Tipo</th>
                            <th>Cant.</th>
                            <th>Profesional</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentMovements as $movement)
                            <tr>
                                <td class="whitespace-nowrap text-sm">{{ $movement->movement_at?->timezone('America/Santiago')->format('d/m H:i') }}</td>
                                <td class="font-medium">{{ $movement->drug?->name ?? '—' }}</td>
                                <td><span class="badge badge-neutral badge-outline badge-sm">{{ $movement->movement_type->label() }}</span></td>
                                <td>{{ $movement->quantity }}</td>
                                <td class="text-sm text-base-content/70">{{ $movement->user?->display_name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-base-content/50">Sin movimientos registrados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card title="Alertas del sistema">
            <div class="space-y-3">
                @forelse ($systemAlerts as $alert)
                    @php
                        $alertIcon = match ($alert->type) {
                            'low_stock' => 'alert',
                            'expiring_soon' => 'calendar',
                            'high_value_waste' => 'waste',
                            default => match ($alert->severity) {
                                'error' => 'alert',
                                'warning' => 'expiration',
                                default => 'info',
                            },
                        };
                        $iconTone = match ($alert->severity) {
                            'error' => 'bg-error/15 text-error',
                            'warning' => 'bg-warning/15 text-warning',
                            default => 'bg-info/15 text-info',
                        };
                    @endphp
                    <div @class([
                        'flex items-start gap-3 rounded-lg border px-4 py-3',
                        'border-error/30 bg-error/10' => $alert->severity === 'error',
                        'border-warning/30 bg-warning/10' => $alert->severity === 'warning',
                        'border-info/30 bg-info/10' => ! in_array($alert->severity, ['error', 'warning'], true),
                    ])>
                        <span @class(['flex size-9 shrink-0 items-center justify-center rounded-lg', $iconTone]) aria-hidden="true">
                            <x-ui.icon :name="$alertIcon" class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold">{{ $alert->title }}</h3>
                            <p class="mt-1 text-xs text-base-content/70">{{ $alert->message }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center rounded-lg border border-success/30 bg-success/10 px-4 py-6 text-center">
                        <span class="mb-2 flex size-10 items-center justify-center rounded-lg bg-success/15 text-success" aria-hidden="true">
                            <x-ui.icon name="check" class="size-5" />
                        </span>
                        <p class="text-sm font-medium text-success">Sin alertas activas</p>
                        <p class="mt-1 text-xs text-base-content/55">El sistema opera con normalidad</p>
                    </div>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
