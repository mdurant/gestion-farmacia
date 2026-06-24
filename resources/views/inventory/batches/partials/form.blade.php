@csrf

<fieldset class="fieldset gap-4">
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.field label="Fecha de vencimiento" for="expiration_date" :error="$errors->first('expiration_date')" required>
            <x-ui.input id="expiration_date" name="expiration_date" type="date"
                        value="{{ old('expiration_date', $batch->expiration_date?->format('Y-m-d')) }}" required />
        </x-ui.field>

        <x-ui.field label="Costo unitario (CLP)" for="unit_cost" :error="$errors->first('unit_cost')" required>
            <x-ui.input id="unit_cost" name="unit_cost" type="number" min="0" step="0.01"
                        value="{{ old('unit_cost', $batch->unit_cost) }}" required />
        </x-ui.field>

        <x-ui.field label="Proveedor" for="supplier_name" :error="$errors->first('supplier_name')">
            <x-ui.input id="supplier_name" name="supplier_name" value="{{ old('supplier_name', $batch->supplier_name) }}" placeholder="Cenabast" />
        </x-ui.field>

        <x-ui.field label="Documento proveedor" for="supplier_document" :error="$errors->first('supplier_document')">
            <x-ui.input id="supplier_document" name="supplier_document" value="{{ old('supplier_document', $batch->supplier_document) }}" />
        </x-ui.field>
    </div>

    <p class="text-xs text-base-content/55">
        El stock ({{ $batch->availableQuantity() }} uds.) y el número de lote solo se modifican mediante movimientos de inventario.
    </p>
</fieldset>
