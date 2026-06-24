@csrf

<fieldset class="fieldset gap-4">
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.field label="RUT" for="rut" :error="$errors->first('rut')" required>
            <x-ui.input id="rut" name="rut" value="{{ old('rut', $resident->rut ?? '') }}" required placeholder="12.345.678-9" />
        </x-ui.field>

        <x-ui.field label="Previsión de salud" for="health_insurance_id" :error="$errors->first('health_insurance_id')">
            <x-ui.select id="health_insurance_id" name="health_insurance_id">
                <option value="">Seleccionar</option>
                @foreach ($healthInsurances as $insurance)
                    <option value="{{ $insurance->id }}" @selected(old('health_insurance_id', $resident->health_insurance_id ?? '') == $insurance->id)>
                        {{ $insurance->name }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.field>

        <x-ui.field label="Nombres" for="first_name" :error="$errors->first('first_name')" required>
            <x-ui.input id="first_name" name="first_name" value="{{ old('first_name', $resident->first_name ?? '') }}" required />
        </x-ui.field>

        <x-ui.field label="Apellidos" for="last_name" :error="$errors->first('last_name')" required>
            <x-ui.input id="last_name" name="last_name" value="{{ old('last_name', $resident->last_name ?? '') }}" required />
        </x-ui.field>

        <x-ui.field label="Fecha de nacimiento" for="birth_date" :error="$errors->first('birth_date')">
            <x-ui.input id="birth_date" name="birth_date" type="date"
                        value="{{ old('birth_date', isset($resident) ? $resident->birth_date?->format('Y-m-d') : '') }}" />
        </x-ui.field>

        <x-ui.field label="Fecha de ingreso" for="admission_date" :error="$errors->first('admission_date')">
            <x-ui.input id="admission_date" name="admission_date" type="date"
                        value="{{ old('admission_date', isset($resident) ? $resident->admission_date?->format('Y-m-d') : '') }}" />
        </x-ui.field>

        <x-ui.field label="Centro de costo / ubicación" for="cost_center_id" :error="$errors->first('cost_center_id')">
            <x-ui.select id="cost_center_id" name="cost_center_id">
                <option value="">Sin asignar</option>
                @foreach ($costCenters as $center)
                    <option value="{{ $center->id }}" @selected(old('cost_center_id', $resident->cost_center_id ?? '') == $center->id)>
                        {{ $center->name }} ({{ $center->code }})
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.field>

        <x-ui.field label="Habitación" for="room_number" :error="$errors->first('room_number')">
            <x-ui.input id="room_number" name="room_number" value="{{ old('room_number', $resident->room_number ?? '') }}" placeholder="101" />
        </x-ui.field>

        <x-ui.field label="Alergias" for="allergies" :error="$errors->first('allergies')" class="md:col-span-2">
            <x-ui.textarea id="allergies" name="allergies" rows="2">{{ old('allergies', $resident->allergies ?? '') }}</x-ui.textarea>
        </x-ui.field>

        <x-ui.field label="Servicio de rescate" for="rescue_service" :error="$errors->first('rescue_service')" class="md:col-span-2">
            <x-ui.textarea id="rescue_service" name="rescue_service" rows="2">{{ old('rescue_service', $resident->rescue_service ?? '') }}</x-ui.textarea>
        </x-ui.field>

        <x-ui.field label="Diagnóstico" for="diagnosis" :error="$errors->first('diagnosis')" class="md:col-span-2">
            <x-ui.textarea id="diagnosis" name="diagnosis" rows="3">{{ old('diagnosis', $resident->diagnosis ?? '') }}</x-ui.textarea>
        </x-ui.field>

        <x-ui.field label="Contacto de emergencia" for="emergency_contact_name" :error="$errors->first('emergency_contact_name')">
            <x-ui.input id="emergency_contact_name" name="emergency_contact_name"
                        value="{{ old('emergency_contact_name', $resident->emergency_contact_name ?? '') }}" />
        </x-ui.field>

        <x-ui.field label="Teléfono emergencia" for="emergency_contact_phone" :error="$errors->first('emergency_contact_phone')">
            <x-ui.input id="emergency_contact_phone" name="emergency_contact_phone" type="tel"
                        value="{{ old('emergency_contact_phone', $resident->emergency_contact_phone ?? '') }}" />
        </x-ui.field>

        <x-ui.field label="Notas clínicas adicionales" for="medical_notes" :error="$errors->first('medical_notes')" class="md:col-span-2">
            <x-ui.textarea id="medical_notes" name="medical_notes" rows="3">{{ old('medical_notes', $resident->medical_notes ?? '') }}</x-ui.textarea>
        </x-ui.field>
    </div>

    <label class="fieldset-label cursor-pointer gap-3 rounded-lg border border-base-300 bg-base-200/50 px-3 py-2.5">
        <input type="hidden" name="is_active" value="0" />
        <input type="checkbox" name="is_active" value="1" class="toggle toggle-success"
               @checked(old('is_active', $resident->is_active ?? true)) />
        <span>Residente activo en la residencia</span>
    </label>

    <p class="text-xs text-base-content/55">
        Los datos identificatorios y clínicos se almacenan cifrados. Cada consulta, alta, edición o baja queda registrada con usuario, fecha, navegador y valores auditados.
    </p>
</fieldset>
