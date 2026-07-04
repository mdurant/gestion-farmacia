@extends('layouts.app')

@section('title', 'Gráficos analíticos')
@section('page-title', 'Gráficos analíticos')
@section('page-subtitle', 'Inventario, consumo, proveedores y auditoría de pérdidas')

@section('content')
<div class="space-y-8">
    <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">← Reportes</a>

    @include('reports.partials.filters', [
        'showResidentFilter' => false,
        'showDrugFilter' => true,
    ])

    <div class="alert alert-info text-sm">
        Los gráficos se adaptan a la lógica institucional de Acalis Pharma:
        <strong>consumo por administración</strong> (no venta retail),
        <strong>fármacos controlados vs no controlados</strong>,
        <strong>proveedores de lotes</strong> y
        <strong>mermas/vencimientos</strong> como pérdidas.
    </div>

    {{-- 1. Inventario y caducidad --}}
    <section class="space-y-4">
        <div>
            <h2 class="text-lg font-semibold">1. Control de inventario y caducidad</h2>
            <p class="text-sm text-base-content/60">Valor de stock frente al mínimo por categoría y riesgo de vencimiento.</p>
        </div>
        <div class="grid gap-6 xl:grid-cols-2">
            <x-ui.card title="Valor inventario vs stock mínimo por categoría">
                <p class="mb-3 text-xs text-base-content/55">Barras agrupadas · valor actual del inventario frente al valor del stock mínimo requerido.</p>
                <div class="relative h-72">
                    <canvas id="chart-inventory-category" aria-label="Gráfico de inventario por categoría"></canvas>
                </div>
            </x-ui.card>

            <x-ui.card title="Riesgo de caducidad (≤ 90 días)">
                <p class="mb-3 text-xs text-base-content/55">
                    Medidor · porcentaje de unidades próximas a vencer.
                    Verde ≤ {{ $charts['expiry_gauge']['thresholds']['green'] }}%,
                    amarillo ≤ {{ $charts['expiry_gauge']['thresholds']['yellow'] }}%,
                    rojo &gt; {{ $charts['expiry_gauge']['thresholds']['yellow'] }}%.
                </p>
                <div class="relative h-56">
                    <canvas id="chart-expiry-gauge" aria-label="Medidor de caducidad"></canvas>
                </div>
                <div id="expiry-gauge-meta" class="mt-3 flex flex-wrap gap-2"></div>
            </x-ui.card>
        </div>
    </section>

    {{-- 2. Consumo y rentabilidad operativa --}}
    <section class="space-y-4">
        <div>
            <h2 class="text-lg font-semibold">2. Consumo y rentabilidad operativa</h2>
            <p class="text-sm text-base-content/60">
                Equivalente institucional a ventas: administraciones a residentes.
                RX = controlados/psicotrópicos · Non-RX = resto del catálogo.
            </p>
        </div>
        <div class="grid gap-6 xl:grid-cols-2">
            <x-ui.card title="Tendencia de consumo RX vs Non-RX">
                <p class="mb-3 text-xs text-base-content/55">Líneas con marcadores · valor administrado (CLP) por mes.</p>
                <div class="relative h-72">
                    <canvas id="chart-consumption-trend" aria-label="Tendencia de consumo"></canvas>
                </div>
            </x-ui.card>

            <x-ui.card title="Rotación vs eficiencia operativa">
                <p class="mb-3 text-xs text-base-content/55">
                    Burbujas · eje X = unidades administradas, eje Y = consumo/stock (%),
                    tamaño ≈ costo unitario. Productos “estrella” arriba a la derecha.
                </p>
                <div class="relative h-72">
                    <canvas id="chart-rotation-bubble" aria-label="Rotación y eficiencia"></canvas>
                </div>
            </x-ui.card>
        </div>
    </section>

    {{-- 3. Compras y proveedores --}}
    <section class="space-y-4">
        <div>
            <h2 class="text-lg font-semibold">3. Control de compras y proveedores</h2>
            <p class="text-sm text-base-content/60">Basado en entradas de inventario y proveedor registrado en cada lote.</p>
        </div>
        <div class="grid gap-6 xl:grid-cols-2">
            <x-ui.card title="Proveedores: reposición vs cumplimiento">
                <p class="mb-3 text-xs text-base-content/55">
                    Dispersión · X = días promedio entre recepciones (ciclo de reposición),
                    Y = % administrado sobre salidas (sin merma/vencimiento).
                    Ideal: izquierda y arriba (rápido y confiable).
                </p>
                <div class="relative h-72">
                    <canvas id="chart-supplier-scatter" aria-label="Dispersión de proveedores"></canvas>
                </div>
            </x-ui.card>

            <x-ui.card title="Distribución de compras por proveedor">
                <p class="mb-3 text-xs text-base-content/55">Dona · valor de entradas (CLP) por proveedor. Detecta dependencia de un solo distribuidor.</p>
                <div class="relative h-72">
                    <canvas id="chart-purchases-donut" aria-label="Compras por proveedor"></canvas>
                </div>
            </x-ui.card>
        </div>
    </section>

    {{-- 4. Auditoría y prevención de pérdidas --}}
    <section class="space-y-4">
        <div>
            <h2 class="text-lg font-semibold">4. Auditoría y prevención de pérdidas</h2>
            <p class="text-sm text-base-content/60">Mermas, vencimientos y flujo de valor del inventario.</p>
        </div>
        <div class="grid gap-6 xl:grid-cols-2">
            <x-ui.card title="Control estadístico de pérdidas (Shewhart)">
                <p class="mb-3 text-xs text-base-content/55">
                    Línea base · valor diario de merma + vencimiento, media y límites ±2σ.
                    Puntos fuera de control se marcan en rojo.
                </p>
                <div class="relative h-72">
                    <canvas id="chart-loss-control" aria-label="Control de pérdidas"></canvas>
                </div>
                <div id="loss-control-alerts" class="mt-3 flex flex-wrap gap-2"></div>
            </x-ui.card>

            <x-ui.card title="Embudo de flujo de inventario">
                <p class="mb-3 text-xs text-base-content/55">
                    Embudo · valor desde recepción hasta administraciones y pérdidas.
                    Equivalente institucional a la auditoría de facturación retail.
                </p>
                <div class="relative h-72">
                    <canvas id="chart-movement-funnel" aria-label="Embudo de movimientos"></canvas>
                </div>
            </x-ui.card>
        </div>
    </section>
</div>

<script id="reports-charts-data" type="application/json">
{!! json_encode($charts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>
@endsection

@push('scripts')
    @vite('resources/js/reports-charts.js')
@endpush
