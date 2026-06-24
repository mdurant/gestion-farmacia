@csrf

<fieldset class="fieldset gap-4">
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.field label="Código" for="code" :error="$errors->first('code')" required>
            <x-ui.input id="code" name="code" value="{{ old('code', $pharmacy->code ?? '') }}" required placeholder="BC-001" />
        </x-ui.field>

        <x-ui.field label="Nombre" for="name" :error="$errors->first('name')" required>
            <x-ui.input id="name" name="name" value="{{ old('name', $pharmacy->name ?? '') }}" required />
        </x-ui.field>

        <x-ui.field label="Tipo de bodega" for="type" :error="$errors->first('type')" required>
            <x-ui.select id="type" name="type" required>
                <option value="">Seleccionar tipo</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $pharmacy->type->value ?? '') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.field>

        <x-ui.field label="Centro de costo" for="cost_center_id" :error="$errors->first('cost_center_id')" required>
            <x-ui.select id="cost_center_id" name="cost_center_id" required>
                <option value="">Seleccionar centro</option>
                @foreach ($costCenters as $center)
                    <option value="{{ $center->id }}" @selected(old('cost_center_id', $pharmacy->cost_center_id ?? '') == $center->id)>
                        {{ $center->name }} ({{ $center->code }})
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.field>

        <x-ui.field label="Descripción" for="description" :error="$errors->first('description')" class="md:col-span-2">
            <textarea id="description" name="description" class="textarea vx-control w-full" rows="3">{{ old('description', $pharmacy->description ?? '') }}</textarea>
        </x-ui.field>
    </div>

    <label class="fieldset-label cursor-pointer gap-3 rounded-lg border border-base-300 bg-base-200/50 px-3 py-2.5">
        <input type="hidden" name="is_active" value="0" />
        <input type="checkbox" name="is_active" value="1" class="toggle toggle-success"
               @checked(old('is_active', $pharmacy->is_active ?? true)) />
        <span>Bodega activa</span>
    </label>
</fieldset>
