@csrf

<fieldset class="fieldset gap-4">
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.field label="Código" for="code" :error="$errors->first('code')" required>
            <x-ui.input id="code" name="code" value="{{ old('code', $costCenter->code ?? '') }}" required placeholder="PISO-1" />
        </x-ui.field>

        <x-ui.field label="Nombre" for="name" :error="$errors->first('name')" required>
            <x-ui.input id="name" name="name" value="{{ old('name', $costCenter->name ?? '') }}" required />
        </x-ui.field>

        <x-ui.field label="Piso" for="floor" :error="$errors->first('floor')">
            <x-ui.input id="floor" name="floor" value="{{ old('floor', $costCenter->floor ?? '') }}" placeholder="1" />
        </x-ui.field>

        <x-ui.field label="Pabellón" for="pavilion" :error="$errors->first('pavilion')">
            <x-ui.input id="pavilion" name="pavilion" value="{{ old('pavilion', $costCenter->pavilion ?? '') }}" placeholder="A" />
        </x-ui.field>

        <x-ui.field label="Descripción" for="description" :error="$errors->first('description')" class="md:col-span-2">
            <textarea id="description" name="description" class="textarea vx-control w-full" rows="3">{{ old('description', $costCenter->description ?? '') }}</textarea>
        </x-ui.field>
    </div>

    <label class="fieldset-label cursor-pointer gap-3 rounded-lg border border-base-300 bg-base-200/50 px-3 py-2.5">
        <input type="hidden" name="is_active" value="0" />
        <input type="checkbox" name="is_active" value="1" class="toggle toggle-success"
               @checked(old('is_active', $costCenter->is_active ?? true)) />
        <span>Centro de costo activo</span>
    </label>
</fieldset>
