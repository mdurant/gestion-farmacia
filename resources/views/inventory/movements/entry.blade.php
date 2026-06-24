@extends('layouts.app')

@section('title', 'Entrada de inventario')
@section('page-title', 'Registrar entrada')
@section('page-subtitle', 'Recepción de fármacos y nuevos lotes')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm mb-4">← Inventario</a>
    <x-ui.card>
        <form method="POST" action="{{ route('inventory.movements.entry.store') }}" class="space-y-6">
            @csrf

            <div class="vx-form-section">
                <p class="vx-form-section-title">Recepción</p>
                <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Fármaco" for="drug_id" :error="$errors->first('drug_id')" required>
                    <x-ui.select id="drug_id" name="drug_id" required>
                        <option value="">Seleccionar fármaco</option>
                        @foreach ($drugs as $drug)
                            <option value="{{ $drug->id }}" @selected(old('drug_id') == $drug->id)>{{ $drug->name }} ({{ $drug->code }})</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Bodega destino" for="pharmacy_id" :error="$errors->first('pharmacy_id')" required>
                    <x-ui.select id="pharmacy_id" name="pharmacy_id" required>
                        <option value="">Seleccionar bodega</option>
                        @foreach ($pharmacies as $pharmacy)
                            <option value="{{ $pharmacy->id }}" @selected(old('pharmacy_id') == $pharmacy->id)>{{ $pharmacy->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Centro de costo" for="cost_center_id" :error="$errors->first('cost_center_id')" required>
                    <x-ui.select id="cost_center_id" name="cost_center_id" required>
                        <option value="">Seleccionar centro</option>
                        @foreach ($costCenters as $center)
                            <option value="{{ $center->id }}" @selected(old('cost_center_id') == $center->id)>{{ $center->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Número de lote" for="batch_number" :error="$errors->first('batch_number')" required>
                    <x-ui.input id="batch_number" name="batch_number" value="{{ old('batch_number') }}" required placeholder="L-2026-001" />
                </x-ui.field>

                <x-ui.field label="Fecha de vencimiento" for="expiration_date" :error="$errors->first('expiration_date')" required>
                    <x-ui.input id="expiration_date" name="expiration_date" type="date" value="{{ old('expiration_date') }}" required />
                </x-ui.field>

                <x-ui.field label="Cantidad" for="quantity" :error="$errors->first('quantity')" required>
                    <x-ui.input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity') }}" required />
                </x-ui.field>

                <x-ui.field label="Costo unitario (CLP)" for="unit_cost" :error="$errors->first('unit_cost')" required>
                    <x-ui.input id="unit_cost" name="unit_cost" type="number" min="0" step="0.01" value="{{ old('unit_cost') }}" required />
                </x-ui.field>

                <x-ui.field label="Proveedor" for="supplier_name" :error="$errors->first('supplier_name')">
                    <x-ui.input id="supplier_name" name="supplier_name" value="{{ old('supplier_name') }}" placeholder="Cenabast" />
                </x-ui.field>

                <x-ui.field label="Documento proveedor" for="supplier_document" :error="$errors->first('supplier_document')">
                    <x-ui.input id="supplier_document" name="supplier_document" value="{{ old('supplier_document') }}" />
                </x-ui.field>
            </div>
            </div>

            <x-ui.field label="Notas" for="notes" :error="$errors->first('notes')">
                <x-ui.textarea id="notes" name="notes" rows="2">{{ old('notes') }}</x-ui.textarea>
            </x-ui.field>

            <div class="form-actions pt-4">
                <button type="submit" class="btn btn-primary">Registrar entrada</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
