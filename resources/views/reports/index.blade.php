@extends('layouts.app')

@section('title', 'Reportes')
@section('page-title', 'Reportes')
@section('page-subtitle', 'Análisis operacional y gerencial del inventario farmacéutico')

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 lg:grid-cols-2">
        <x-ui.card title="Reportes internos">
            <p class="mb-4 text-sm text-base-content/70">Operación clínica y trazabilidad diaria.</p>
            <div class="space-y-3">
                @foreach ([\App\Enums\ReportType::Kardex, \App\Enums\ReportType::ResidentConsumption] as $report)
                    <a href="{{ route($report->routeName()) }}" class="flex items-start gap-3 rounded-lg border border-base-300 bg-base-200/40 p-4 transition hover:border-primary/40 hover:bg-primary/5">
                        <span class="badge badge-primary badge-outline badge-sm mt-0.5">Interno</span>
                        <span>
                            <span class="font-semibold">{{ $report->label() }}</span>
                            <span class="mt-1 block text-sm text-base-content/65">{{ $report->description() }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </x-ui.card>

        @can('reports.executive')
            <x-ui.card title="Reportes gerenciales">
                <p class="mb-4 text-sm text-base-content/70">Valorización, mermas, proyección de compras y gráficos analíticos.</p>
                <div class="space-y-3">
                    @foreach ([
                        \App\Enums\ReportType::Charts,
                        \App\Enums\ReportType::Valuation,
                        \App\Enums\ReportType::MonthlyWaste,
                        \App\Enums\ReportType::PurchaseProjection,
                    ] as $report)
                        <a href="{{ route($report->routeName()) }}" class="flex items-start gap-3 rounded-lg border border-base-300 bg-base-200/40 p-4 transition hover:border-secondary/40 hover:bg-secondary/5">
                            <span class="badge badge-secondary badge-outline badge-sm mt-0.5">Gerencia</span>
                            <span>
                                <span class="font-semibold">{{ $report->label() }}</span>
                                <span class="mt-1 block text-sm text-base-content/65">{{ $report->description() }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
        @else
            <x-ui.card title="Reportes gerenciales">
                <p class="text-sm text-base-content/60">Valorización, mermas mensuales y proyección de compra requieren permiso gerencial.</p>
            </x-ui.card>
        @endcan
    </div>
</div>
@endsection
