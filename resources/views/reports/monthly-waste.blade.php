@extends('layouts.app')

@section('title', 'Mermas mensuales')
@section('page-title', 'Mermas mensuales')
@section('page-subtitle', 'Reporte gerencial · pérdidas por período')

@section('content')
<div class="space-y-6">
    <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">← Reportes</a>

    @include('reports.partials.filters', [
        'exportReport' => 'mermas-mensuales',
        'showDrugFilter' => false,
        'showResidentFilter' => false,
    ])

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Mes</th>
                        <th>Movimientos</th>
                        <th>Cantidad total</th>
                        <th>Valor total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td class="font-medium">{{ $row->month_label }}</td>
                            <td>{{ $row->movements_count }}</td>
                            <td>{{ $row->total_quantity }}</td>
                            <td class="font-semibold text-error">${{ number_format($row->total_value, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-base-content/50">Sin mermas en el período seleccionado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
@endsection
