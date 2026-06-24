<x-guest-layout>
    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    @if (request('sesion') === 'reemplazada')
        <div class="alert alert-warning mb-4">
            Su sesión fue cerrada porque se inició sesión desde otro dispositivo.
        </div>
    @endif

    <h2 class="mb-1 text-xl font-bold text-base-content">Iniciar sesión</h2>
    <p class="mb-6 text-sm text-base-content/60">Ingrese sus credenciales institucionales.</p>

    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf

        <fieldset class="fieldset gap-4">
            <x-ui.field label="Correo electrónico" for="email" :error="$errors->first('email')" required>
                <x-ui.input id="email" type="email" name="email" value="{{ old('email') }}"
                            required autofocus autocomplete="username" />
            </x-ui.field>

            <x-ui.field label="Contraseña" for="password" :error="$errors->first('password')" required>
                <x-ui.input id="password" type="password" name="password"
                            required autocomplete="current-password" />
            </x-ui.field>

            <label class="fieldset-label cursor-pointer justify-start gap-3 rounded-lg border border-base-300 bg-base-200/50 px-3 py-2.5">
                <input id="remember_me" type="checkbox" name="remember" value="1"
                       class="checkbox checkbox-primary checkbox-sm"
                       @checked(old('remember')) />
                <span>Recordarme en este equipo</span>
            </label>

            <div class="rounded-lg border border-base-300 bg-base-200/40 px-3 py-3">
                <label class="fieldset-label cursor-pointer items-start justify-start gap-3">
                    <input id="terms_accepted" type="checkbox" name="terms_accepted" value="1"
                           class="checkbox checkbox-primary checkbox-sm mt-0.5"
                           @checked(old('terms_accepted'))
                           required />
                    <span class="text-sm leading-relaxed">
                        He leído y acepto los
                        <button type="button" class="link link-primary font-medium"
                                onclick="document.getElementById('terms-dialog').showModal()">
                            términos y condiciones de uso
                        </button>
                        de la plataforma, incluido el tratamiento de datos personales
                        (versión {{ $termsVersion }}).
                    </span>
                </label>
                @error('terms_accepted')
                    <p class="mt-2 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-actions flex-col pt-2 sm:flex-row sm:justify-between">
                <div class="flex flex-col gap-1 text-sm">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link link-primary">¿Olvidó su contraseña?</a>
                    @endif
                    @if (Route::has('activation.request'))
                        <a href="{{ route('activation.request') }}" class="link link-primary">Activar cuenta nueva</a>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary w-full sm:ms-auto sm:w-auto">Ingresar</button>
            </div>
        </fieldset>
    </form>

    <x-auth.terms-modal :terms-version="$termsVersion" />
    <x-auth.close-other-devices-modal :active-session="session('active_session_info')" />

    @if (app()->environment('local'))
        <div class="mt-8 rounded-xl border border-base-300 bg-base-200/40 p-4">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-base-content/50">Cuentas demo</p>
            <p class="mb-3 text-xs text-base-content/55">
                Todas las notificaciones y alertas por correo llegan a
                <strong>{{ \App\Support\DemoAccounts::notificationInbox() }}</strong>
                (alias Gmail <kbd class="kbd kbd-xs">+rol</kbd> para iniciar sesión).
            </p>
            <div class="space-y-2">
                @foreach (\App\Support\DemoAccounts::loginPanel() as $demo)
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 rounded-lg border border-base-300 bg-base-100 px-3 py-2 text-left transition hover:border-primary/40 hover:bg-primary/5"
                        x-data
                        @click="document.getElementById('email').value = '{{ $demo['email'] }}'; document.getElementById('password').value = 'password'; document.getElementById('terms_accepted').checked = true;"
                    >
                        <x-ui.avatar :name="$demo['name']" size="sm" ring />
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium">{{ $demo['name'] }}</span>
                            <span class="block truncate text-xs text-base-content/55">{{ $demo['email'] }} · {{ $demo['role'] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-base-content/45">Contraseña demo: <kbd class="kbd kbd-xs">password</kbd></p>
        </div>
    @endif
</x-guest-layout>
