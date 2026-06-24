<section>
    <header class="mb-5">
        <h2 class="text-lg font-semibold">Información personal</h2>
        <p class="text-sm text-base-content/60">Datos protegidos con cifrado en reposo (RUT y nombres).</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <fieldset class="fieldset gap-4">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Nombre" for="first_name" :error="$errors->first('first_name')" required>
                    <x-ui.input id="first_name" name="first_name" type="text"
                                value="{{ old('first_name', $user->first_name) }}" required autocomplete="given-name" />
                </x-ui.field>

                <x-ui.field label="Apellido" for="last_name" :error="$errors->first('last_name')" required>
                    <x-ui.input id="last_name" name="last_name" type="text"
                                value="{{ old('last_name', $user->last_name) }}" required autocomplete="family-name" />
                </x-ui.field>

                <x-ui.field label="RUT" for="rut" :error="$errors->first('rut')" hint="Formato: 12.345.678-5" required>
                    <x-ui.input id="rut" name="rut" type="text"
                                value="{{ old('rut', $user->rut) }}" required />
                </x-ui.field>

                <x-ui.field label="Correo electrónico" for="email" :error="$errors->first('email')" required>
                    <x-ui.input id="email" name="email" type="email"
                                value="{{ old('email', $user->email) }}" required autocomplete="email" />
                </x-ui.field>
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning">
                    <span>Su correo no está verificado.</span>
                    <button form="send-verification" type="submit" class="btn btn-sm btn-outline">Reenviar verificación</button>
                </div>
            @endif

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                @if (session('status') === 'profile-updated')
                    <span class="text-sm text-success">Información actualizada correctamente.</span>
                @endif
            </div>
        </fieldset>
    </form>
</section>
