<x-guest-layout>
    <h2 class="mb-1 text-xl font-bold text-base-content">Definir contraseña</h2>
    <p class="mb-6 text-sm text-base-content/60">
        Último paso: elija una contraseña segura para acceder al sistema.
    </p>

    <form method="POST" action="{{ route('activation.complete') }}">
        @csrf

        <fieldset class="fieldset gap-4">
            <x-ui.field label="Contraseña" for="password" :error="$errors->first('password')" required>
                <x-ui.input id="password" type="password" name="password"
                            required autofocus autocomplete="new-password" />
            </x-ui.field>

            <x-ui.field label="Confirmar contraseña" for="password_confirmation" required>
                <x-ui.input id="password_confirmation" type="password" name="password_confirmation"
                            required autocomplete="new-password" />
            </x-ui.field>

            <div class="form-actions pt-2">
                <button type="submit" class="btn btn-primary w-full">Activar cuenta</button>
            </div>
        </fieldset>
    </form>
</x-guest-layout>
