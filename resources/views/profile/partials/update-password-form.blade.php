<section>
    <header class="mb-5">
        <h2 class="text-lg font-semibold">Actualizar contraseña</h2>
        <p class="text-sm text-base-content/60">Use una contraseña segura y distinta a la anterior.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <fieldset class="fieldset gap-4">
            <x-ui.field label="Contraseña actual" for="update_password_current_password"
                        :error="$errors->updatePassword->first('current_password')">
                <x-ui.input id="update_password_current_password" name="current_password" type="password"
                            autocomplete="current-password" />
            </x-ui.field>

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Nueva contraseña" for="update_password_password"
                            :error="$errors->updatePassword->first('password')">
                    <x-ui.input id="update_password_password" name="password" type="password"
                                autocomplete="new-password" />
                </x-ui.field>

                <x-ui.field label="Confirmar contraseña" for="update_password_password_confirmation">
                    <x-ui.input id="update_password_password_confirmation" name="password_confirmation"
                                type="password" autocomplete="new-password" />
                </x-ui.field>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
                @if (session('status') === 'password-updated')
                    <span class="text-sm text-success">Contraseña actualizada correctamente.</span>
                @endif
            </div>
        </fieldset>
    </form>
</section>
