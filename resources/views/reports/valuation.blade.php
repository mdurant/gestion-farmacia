@extends('layouts.app')

@section('title', 'Valorización')
@section('page-title', 'Valorización de inventario')
@section('page-subtitle', 'Reporte gerencial · stock valorizado')

@section('content')
<div class="space-y-6">
    <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">← Reportes</a>

    @include('reports.partials.filters', [
        'exportReport' => 'valorizacion',
        'showResidentFilter' => false,
    ])

    <x-ui.records-found :count="$data['by_drug']->count()" />

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Valor total" :value="'$'.number_format($data['total_value'], 0, ',', '.')" color="primary" icon="chart" />
        <x-ui.stat-card label="Unidades" :value="$data['total_units']" color="success" icon="box" />
        <x-ui.stat-card label="Lotes activos" :value="$data['batch_count']" color="info" icon="activity" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Por bodega">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead><tr class="text-base-content/60"><th>Bodega</th><th>Unidades</th><th>Valor</th></tr></thead>
                    <tbody>
                        @forelse ($data['by_pharmacy'] as $row)
                            <tr>
                                <td>{{ $row->pharmacy_name }}</td>
                                <td>{{ $row->total_units }}</td>
                                <td>${{ number_format($row->total_value, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-center text-base-content/50">Sin stock</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card title="Por fármaco">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead><tr class="text-base-content/60"><th>Fármaco</th><th>Unidades</th><th>Valor</th></tr></thead>
                    <tbody>
                        @forelse ($data['by_drug'] as $row)
                            <tr>
                                <td>{{ $row->drug_name }}</td>
                                <td>{{ $row->total_units }}</td>
                                <td>${{ number_format($row->total_value, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-center text-base-content/50">Sin stock</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
