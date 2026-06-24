@csrf

<fieldset class="fieldset gap-4">
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.field label="Nombre" for="first_name" :error="$errors->first('first_name')" required>
            <x-ui.input id="first_name" name="first_name" type="text"
                        value="{{ old('first_name', $user->first_name ?? '') }}" required />
        </x-ui.field>

        <x-ui.field label="Apellido" for="last_name" :error="$errors->first('last_name')" required>
            <x-ui.input id="last_name" name="last_name" type="text"
                        value="{{ old('last_name', $user->last_name ?? '') }}" required />
        </x-ui.field>

        <x-ui.field label="RUT" for="rut" :error="$errors->first('rut')" hint="Formato: 12.345.678-5" required>
            <x-ui.input id="rut" name="rut" type="text" placeholder="12.345.678-5"
                        value="{{ old('rut', $user->rut ?? '') }}" required />
        </x-ui.field>

        <x-ui.field label="Correo electrónico" for="email" :error="$errors->first('email')" required>
            <x-ui.input id="email" name="email" type="email"
                        value="{{ old('email', $user->email ?? '') }}" required autocomplete="email" />
        </x-ui.field>

        <x-ui.field label="Rol" for="role" :error="$errors->first('role')" required>
            <x-ui.select id="role" name="role" required>
                <option value="">Seleccionar rol</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected(old('role', isset($user) ? $user->role?->value : '') === $role->value)>
                        {{ $role->label() }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.field>

        @isset($user)
            <x-ui.field label="Nueva contraseña (opcional)" for="password" :error="$errors->first('password')">
                <x-ui.input id="password" name="password" type="password" autocomplete="new-password" />
            </x-ui.field>

            <x-ui.field label="Confirmar contraseña" for="password_confirmation">
                <x-ui.input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            </x-ui.field>
        @endisset
    </div>

    @isset($user)
        <label class="fieldset-label cursor-pointer justify-start gap-3 rounded-xl border border-base-300 bg-base-200/50 px-4 py-3 md:col-span-2">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1" class="toggle toggle-success"
                   @checked(old('is_active', $user->is_active ?? true)) />
            <span>Usuario activo — puede iniciar sesión en el sistema</span>
        </label>
    @else
        <div class="rounded-xl border border-info/30 bg-info/5 px-4 py-3 text-sm text-base-content/70 md:col-span-2">
            El usuario recibirá un código de 6 dígitos por correo para activar su cuenta y definir su contraseña.
            No se almacena ni transmite una contraseña temporal.
        </div>
    @endisset
</fieldset>
