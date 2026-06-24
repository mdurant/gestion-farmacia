@props(['pharmacies', 'costCenters', 'batches', 'pharmacyField' => 'pharmacy_id'])

<div class="grid gap-4 md:grid-cols-2" x-data="{
    pharmacyId: '{{ old($pharmacyField, '') }}',
    batchId: '{{ old('batch_id', '') }}',
    batches: {{ $batches->map(fn ($b) => [
        'id' => $b->id,
        'pharmacy_id' => $b->pharmacy_id,
        'label' => $b->drug?->name . ' · Lote ' . $b->batch_number . ' (' . $b->availableQuantity() . ' uds.)',
        'controlled' => $b->drug?->is_controlled || $b->drug?->is_narcotic,
    ])->values()->toJson() }},
    filteredBatches() {
        if (!this.pharmacyId) return this.batches;
        return this.batches.filter(b => String(b.pharmacy_id) === String(this.pharmacyId));
    }
}">
    <x-ui.field label="Bodega" for="{{ $pharmacyField }}" :error="$errors->first($pharmacyField)" required>
        <x-ui.select id="{{ $pharmacyField }}" name="{{ $pharmacyField }}" required native x-model="pharmacyId" @change="batchId = ''">
            <option value="">Seleccionar bodega</option>
            @foreach ($pharmacies as $pharmacy)
                <option value="{{ $pharmacy->id }}" @selected(old($pharmacyField) == $pharmacy->id)>{{ $pharmacy->name }}</option>
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

    <x-ui.field label="Lote" for="batch_id" :error="$errors->first('batch_id')" required class="md:col-span-2">
        <x-ui.select id="batch_id" name="batch_id" required native x-model="batchId">
            <option value="">Seleccionar lote disponible</option>
            <template x-for="batch in filteredBatches()" :key="batch.id">
                <option :value="batch.id" x-text="batch.label"></option>
            </template>
        </x-ui.select>
    </x-ui.field>

    <x-ui.field label="Código de autorización" for="authorization_code"
                :error="$errors->first('authorization_code')"
                hint="Requerido para fármacos controlados si no tiene permiso de autorización."
                class="md:col-span-2">
        <x-ui.input id="authorization_code" name="authorization_code" type="text"
                    value="{{ old('authorization_code') }}" placeholder="Ej: FAR-12345678" />
    </x-ui.field>
</div>
