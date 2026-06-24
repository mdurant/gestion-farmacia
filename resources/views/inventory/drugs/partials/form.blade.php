@csrf

<fieldset class="fieldset gap-4">
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.field label="Código" for="code" :error="$errors->first('code')" required>
            <x-ui.input id="code" name="code" value="{{ old('code', $drug->code ?? '') }}" required placeholder="FAR-001" />
        </x-ui.field>

        <x-ui.field label="Nombre" for="name" :error="$errors->first('name')" required>
            <x-ui.input id="name" name="name" value="{{ old('name', $drug->name ?? '') }}" required />
        </x-ui.field>

        <x-ui.field label="Categoría" for="category" :error="$errors->first('category')">
            <x-ui.input id="category" name="category" value="{{ old('category', $drug->category ?? '') }}" placeholder="Analgésico" />
        </x-ui.field>

        <x-ui.field label="Presentación" for="presentation" :error="$errors->first('presentation')">
            <x-ui.input id="presentation" name="presentation" value="{{ old('presentation', $drug->presentation ?? '') }}" placeholder="Comprimido" />
        </x-ui.field>

        <x-ui.field label="Principio activo" for="active_ingredient" :error="$errors->first('active_ingredient')" class="md:col-span-2">
            <x-ui.input id="active_ingredient" name="active_ingredient" value="{{ old('active_ingredient', $drug->active_ingredient ?? '') }}" />
        </x-ui.field>

        <x-ui.field label="Stock mínimo" for="min_stock" :error="$errors->first('min_stock')" required>
            <x-ui.input id="min_stock" name="min_stock" type="number" min="0" value="{{ old('min_stock', $drug->min_stock ?? 0) }}" required />
        </x-ui.field>

        <x-ui.field label="Stock máximo" for="max_stock" :error="$errors->first('max_stock')">
            <x-ui.input id="max_stock" name="max_stock" type="number" min="0" value="{{ old('max_stock', $drug->max_stock ?? '') }}" />
        </x-ui.field>

        <x-ui.field label="Costo unitario referencial (CLP)" for="unit_cost" :error="$errors->first('unit_cost')" required>
            <x-ui.input id="unit_cost" name="unit_cost" type="number" min="0" step="0.01" value="{{ old('unit_cost', $drug->unit_cost ?? 0) }}" required />
        </x-ui.field>
    </div>

    <div class="grid gap-3 md:grid-cols-3">
        <label class="fieldset-label cursor-pointer gap-3 rounded-lg border border-base-300 bg-base-200/50 px-3 py-2.5">
            <input type="hidden" name="is_controlled" value="0" />
            <input type="checkbox" name="is_controlled" value="1" class="checkbox checkbox-error checkbox-sm"
                   @checked(old('is_controlled', $drug->is_controlled ?? false)) />
            <span>Fármaco controlado</span>
        </label>
        <label class="fieldset-label cursor-pointer gap-3 rounded-lg border border-base-300 bg-base-200/50 px-3 py-2.5">
            <input type="hidden" name="is_narcotic" value="0" />
            <input type="checkbox" name="is_narcotic" value="1" class="checkbox checkbox-error checkbox-sm"
                   @checked(old('is_narcotic', $drug->is_narcotic ?? false)) />
            <span>Estupefaciente</span>
        </label>
        <label class="fieldset-label cursor-pointer gap-3 rounded-lg border border-base-300 bg-base-200/50 px-3 py-2.5">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-success checkbox-sm"
                   @checked(old('is_active', $drug->is_active ?? true)) />
            <span>Activo en catálogo</span>
        </label>
    </div>
</fieldset>
