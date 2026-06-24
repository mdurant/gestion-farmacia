@extends('layouts.app')

@section('title', 'Kardex')
@section('page-title', 'Kardex de movimientos')
@section('page-subtitle', 'Reporte interno · trazabilidad global')

@section('content')
<div class="space-y-6">
    <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">← Reportes</a>

    @include('reports.partials.filters', [
        'exportReport' => 'kardex',
        'showResidentFilter' => true,
    ])

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
                        <th>Centro</th>
                        <th>Profesional</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="whitespace-nowrap">{{ $movement->movement_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge-neutral badge-outline badge-xs">{{ $movement->movement_type->label() }}</span></td>
                            <td>{{ $movement->drug?->name }}</td>
                            <td class="font-mono text-xs">{{ $movement->batch?->batch_number }}</td>
                            <td>{{ $movement->quantity }}</td>
                            <td>{{ $movement->pharmacy?->name }}</td>
                            <td>{{ $movement->costCenter?->name }}</td>
                            <td>{{ $movement->user?->display_name }}</td>
                            <td>${{ number_format($movement->total_value, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-8 text-center text-base-content/50">Sin movimientos en el período seleccionado</td></tr>
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
