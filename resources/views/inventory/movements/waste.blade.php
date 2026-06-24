@extends('layouts.app')

@section('title', 'Salida por merma')
@section('page-title', 'Registrar merma')
@section('page-subtitle', 'Salida por rotura, derrame u otro desperdicio')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm mb-4">← Inventario</a>
    <x-ui.card>
        <form method="POST" action="{{ route('inventory.movements.waste.store') }}" class="space-y-6">
            @csrf
            @include('inventory.movements.partials.batch-fields', compact('pharmacies', 'costCenters', 'batches'))

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Cantidad" for="quantity" :error="$errors->first('quantity')" required>
                    <x-ui.input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" required />
                </x-ui.field>
            </div>

            <x-ui.field label="Motivo de la merma" for="reason" :error="$errors->first('reason')" required>
                <x-ui.input id="reason" name="reason" value="{{ old('reason') }}" required placeholder="Ej: Frasco roto durante preparación" />
            </x-ui.field>

            <x-ui.field label="Notas adicionales" for="notes" :error="$errors->first('notes')">
                <x-ui.textarea id="notes" name="notes" rows="3">{{ old('notes') }}</x-ui.textarea>
            </x-ui.field>

            <div class="form-actions border-t border-base-300 pt-4">
                <button type="submit" class="btn btn-warning">Registrar merma</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
