@extends('layouts.app')

@section('title', 'Administración a residente')
@section('page-title', 'Administración a residente')
@section('page-subtitle', 'Trazabilidad de medicación entregada')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm mb-4">← Inventario</a>
    <x-ui.card>
        <form method="POST" action="{{ route('inventory.movements.administration.store') }}" class="space-y-6">
            @csrf

            <div class="vx-form-section">
                <p class="vx-form-section-title">Origen del fármaco</p>
                @include('inventory.movements.partials.batch-fields', compact('pharmacies', 'costCenters', 'batches'))
            </div>

            <div class="vx-form-section">
                <p class="vx-form-section-title">Residente y prescripción</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field label="Residente" for="resident_id" :error="$errors->first('resident_id')" required class="md:col-span-2">
                        <x-ui.select id="resident_id" name="resident_id" required>
                            <option value="">Seleccionar residente</option>
                            @foreach ($residents as $resident)
                                <option value="{{ $resident->id }}"
                                    @selected(old('resident_id', $preselectedResidentId ?? '') == $resident->id)>
                                    {{ $resident->full_name }} · Hab. {{ $resident->room_number ?? '—' }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="N° receta / prescripción médica" for="prescription_id"
                                :error="$errors->first('prescription_id')" required
                                hint="Obligatorio para trazabilidad clínica y auditoría.">
                        <x-ui.input id="prescription_id" name="prescription_id" value="{{ old('prescription_id') }}"
                                    required placeholder="RX-2026-001234" />
                    </x-ui.field>

                    <x-ui.field label="Cantidad administrada" for="quantity" :error="$errors->first('quantity')" required>
                        <x-ui.input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" required />
                    </x-ui.field>
                </div>
            </div>

            <x-ui.field label="Notas clínicas" for="notes" :error="$errors->first('notes')">
                <x-ui.textarea id="notes" name="notes" rows="2">{{ old('notes') }}</x-ui.textarea>
            </x-ui.field>

            <div class="form-actions pt-4">
                <button type="submit" class="btn btn-primary">Registrar administración</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
