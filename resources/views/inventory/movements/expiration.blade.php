@extends('layouts.app')

@section('title', 'Salida por vencimiento')
@section('page-title', 'Salida por vencimiento')
@section('page-subtitle', 'Retiro de lotes vencidos o próximos a vencer')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm mb-4">← Inventario</a>
    <x-ui.card>
        <form method="POST" action="{{ route('inventory.movements.expiration.store') }}" class="space-y-6">
            @csrf
            @include('inventory.movements.partials.batch-fields', compact('pharmacies', 'costCenters', 'batches'))

            <x-ui.field label="Cantidad a retirar" for="quantity" :error="$errors->first('quantity')" required>
                <x-ui.input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" required />
            </x-ui.field>

            <x-ui.field label="Notas" for="notes" :error="$errors->first('notes')">
                <x-ui.textarea id="notes" name="notes" rows="2" placeholder="Detalle del retiro por vencimiento">{{ old('notes') }}</x-ui.textarea>
            </x-ui.field>

            <div class="form-actions border-t border-base-300 pt-4">
                <button type="submit" class="btn btn-warning btn-outline">Registrar salida</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
