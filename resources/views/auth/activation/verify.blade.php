<x-guest-layout>
    @if (session('status'))
        <div class="alert alert-info mb-4">{{ session('status') }}</div>
    @endif

    <h2 class="mb-1 text-xl font-bold text-base-content">Verificar código</h2>
    <p class="mb-6 text-sm text-base-content/60">
        Ingrese el código de 6 dígitos enviado a <span class="font-medium text-base-content">{{ $email }}</span>.
    </p>

    <form method="POST" action="{{ route('activation.verify') }}">
        @csrf

        <fieldset class="fieldset gap-4">
            <x-ui.field label="Código de activación" for="code" :error="$errors->first('code')" required>
                <x-ui.input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}"
                            maxlength="6" placeholder="000000" value="{{ old('code') }}"
                            required autofocus autocomplete="one-time-code"
                            class="text-center font-mono text-2xl tracking-[0.35em]" />
            </x-ui.field>

            <div class="form-actions flex-col gap-3 pt-2 sm:flex-row sm:justify-between">
                <a href="{{ route('activation.request') }}" class="link link-primary text-sm">Solicitar nuevo código</a>
                <button type="submit" class="btn btn-primary w-full sm:ms-auto sm:w-auto">Continuar</button>
            </div>
        </fieldset>
    </form>
</x-guest-layout>
