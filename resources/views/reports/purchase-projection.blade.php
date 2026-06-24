@extends('layouts.app')

@section('title', 'Proyección de compra')
@section('page-title', 'Proyección de compra')
@section('page-subtitle', 'Reporte gerencial · reposición sugerida')

@section('content')
<div class="space-y-6">
    <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">← Reportes</a>

    @include('reports.partials.filters', [
        'exportReport' => 'proyeccion-compra',
        'showResidentFilter' => false,
    ])

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Fármaco</th>
                        <th>Stock actual</th>
                        <th>Mínimo</th>
                        <th>Objetivo</th>
                        <th>Compra sugerida</th>
                        <th>Costo estimado</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                <span class="font-medium">{{ $row->drug->name }}</span>
                                <p class="text-xs text-base-content/50">{{ $row->drug->code }}</p>
                            </td>
                            <td @class(['font-semibold text-error' => $row->is_critical])>{{ $row->current_stock }}</td>
                            <td>{{ $row->min_stock }}</td>
                            <td>{{ $row->target_stock }}</td>
                            <td class="font-semibold">{{ $row->suggested_purchase }}</td>
                            <td>${{ number_format($row->estimated_cost, 0, ',', '.') }}</td>
                            <td>
                                @if ($row->is_critical)
                                    <span class="badge badge-error badge-outline badge-sm">Crítico</span>
                                @else
                                    <span class="badge badge-warning badge-outline badge-sm">Reposición</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-base-content/50">Todos los fármacos están dentro del stock objetivo</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
@endsection
