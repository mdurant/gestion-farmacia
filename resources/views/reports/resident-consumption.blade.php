@extends('layouts.app')

@section('title', 'Consumo por residente')
@section('page-title', 'Consumo por residente')
@section('page-subtitle', 'Administraciones agrupadas por ficha clínica')

@section('content')
<div class="space-y-6">
    <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">← Reportes</a>

    @include('reports.partials.filters', [
        'exportReport' => 'consumo-residentes',
        'showDrugFilter' => false,
        'showResidentFilter' => true,
    ])

    <x-ui.records-found :items="$rows" />

    <div class="space-y-4">
        @forelse ($rows as $row)
            <x-ui.card>
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold">{{ $row->resident_name }}</h3>
                        <p class="text-sm text-base-content/60">Habitación {{ $row->room_number ?? '—' }} · {{ $row->administrations_count }} administraciones</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-base-content/60">Total consumido</p>
                        <p class="font-bold">${{ number_format($row->total_value, 0, ',', '.') }} · {{ $row->total_quantity }} uds.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead><tr class="text-base-content/60"><th>Fármaco</th><th>Cantidad</th><th>Valor</th></tr></thead>
                        <tbody>
                            @foreach ($row->drugs as $drug)
                                <tr>
                                    <td>{{ $drug->drug_name }}</td>
                                    <td>{{ $drug->quantity }}</td>
                                    <td>${{ number_format($drug->value, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @empty
            <x-ui.card>
                <p class="py-8 text-center text-base-content/50">Sin administraciones en el período seleccionado</p>
            </x-ui.card>
        @endforelse
    </div>
</div>
@endsection
