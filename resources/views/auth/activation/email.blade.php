<x-guest-layout>
    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    <h2 class="mb-1 text-xl font-bold text-base-content">Activar cuenta</h2>
    <p class="mb-6 text-sm text-base-content/60">
        Ingrese el correo institucional que registró el administrador. Le enviaremos un código de 6 dígitos.
    </p>

    <form method="POST" action="{{ route('activation.send') }}">
        @csrf

        <fieldset class="fieldset gap-4">
            <x-ui.field label="Correo electrónico" for="email" :error="$errors->first('email')" required>
                <x-ui.input id="email" type="email" name="email" value="{{ old('email') }}"
                            required autofocus autocomplete="username" />
            </x-ui.field>

            <div class="form-actions flex-col pt-2 sm:flex-row sm:justify-between">
                <a href="{{ route('login') }}" class="link link-primary text-sm">Volver al inicio de sesión</a>
                <button type="submit" class="btn btn-primary w-full sm:ms-auto sm:w-auto">Enviar código</button>
            </div>
        </fieldset>
    </form>
</x-guest-layout>
