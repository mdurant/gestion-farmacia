@extends('layouts.app')

@section('title', 'Traslado entre bodegas')
@section('page-title', 'Traslado entre bodegas')
@section('page-subtitle', 'Mover stock de una bodega a otra')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm mb-4">← Inventario</a>
    <x-ui.card>
        <form method="POST" action="{{ route('inventory.movements.transfer.store') }}" class="space-y-6">
            @csrf

            <div class="vx-form-section">
                <p class="vx-form-section-title">Origen del stock</p>
                @include('inventory.movements.partials.batch-fields', [
                    'pharmacies' => $pharmacies,
                    'costCenters' => $costCenters,
                    'batches' => $batches,
                    'pharmacyField' => 'source_pharmacy_id',
                ])
            </div>

            <div class="vx-form-section">
                <p class="vx-form-section-title">Destino y cantidad</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Bodega destino" for="destination_pharmacy_id" :error="$errors->first('destination_pharmacy_id')" required class="md:col-span-2">
                        <x-ui.select id="destination_pharmacy_id" name="destination_pharmacy_id" required>
                            <option value="">Seleccionar bodega destino</option>
                            @foreach ($pharmacies as $pharmacy)
                                <option value="{{ $pharmacy->id }}" @selected(old('destination_pharmacy_id') == $pharmacy->id)>{{ $pharmacy->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Cantidad a trasladar" for="quantity" :error="$errors->first('quantity')" required>
                        <x-ui.input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" required />
                    </x-ui.field>

                    <x-ui.field label="Notas" for="notes" :error="$errors->first('notes')" class="md:col-span-2">
                        <x-ui.textarea id="notes" name="notes" rows="2">{{ old('notes') }}</x-ui.textarea>
                    </x-ui.field>
                </div>
            </div>

            <div class="form-actions pt-4">
                <button type="submit" class="btn btn-primary">Registrar traslado</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
