@extends('layouts.app')

@section('title', 'Acceso a datos de residentes')
@section('page-title', 'Protección de datos personales')
@section('page-subtitle', 'Verificación requerida · Ley N° 21.719')

@section('content')
<a href="#resident-gate-dialog" class="vx-skip-link">Saltar al formulario de verificación</a>

<div class="vx-resident-gate-shell" aria-hidden="false">
    <div class="vx-resident-gate-backdrop" aria-hidden="true"></div>

    <div
        id="resident-gate-dialog"
        class="vx-resident-gate-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="resident-gate-title"
        aria-describedby="resident-gate-description"
        tabindex="-1"
    >
        <header class="vx-resident-gate-header">
            <div class="vx-resident-gate-icon" aria-hidden="true">🛡️</div>
            <div>
                <p class="vx-resident-gate-eyebrow">Acceso restringido · Datos sensibles</p>
                <h2 id="resident-gate-title" class="vx-resident-gate-title">Módulo de Residentes</h2>
            </div>
        </header>

        <div id="resident-gate-description" class="vx-resident-gate-body">
            <div class="alert alert-warning text-sm leading-relaxed" role="note">
                <div>
                    <p class="font-semibold">Aviso legal — Ley N° 21.719</p>
                    <p class="mt-2">
                        La información de este módulo constituye <strong>datos personales sensibles</strong>,
                        protegidos por la <strong>Ley N° 21.719</strong> sobre Protección de Datos Personales en Chile.
                        Su acceso está limitado al personal autorizado y debe realizarse con fines clínicos
                        y administrativos estrictamente necesarios.
                    </p>
                    <p class="mt-2">
                        <strong>Toda actividad es auditable</strong>
                    </p>
                </div>
            </div>

            <ul class="vx-resident-gate-checklist" aria-label="Compromisos de acceso">
                <li>Uso exclusivo para funciones autorizadas de su rol</li>
                <li>Prohibida la divulgación a terceros no autorizados</li>
                <li>La sesión de acceso expira en {{ $gateTtlMinutes }} minutos por seguridad</li>
            </ul>
        </div>

        <form
            method="POST"
            action="{{ route('residents.gate.confirm') }}"
            class="vx-resident-gate-form"
            novalidate
        >
            @csrf

            <x-ui.field
                label="Confirmación de lectura"
                for="disclaimer_accepted"
                :error="$errors->first('disclaimer_accepted')"
                required
            >
                <label class="fieldset-label cursor-pointer items-start gap-3 rounded-xl border border-base-300 bg-base-200/40 px-4 py-3">
                    <input
                        type="checkbox"
                        id="disclaimer_accepted"
                        name="disclaimer_accepted"
                        value="1"
                        class="checkbox checkbox-primary checkbox-sm mt-0.5"
                        @checked(old('disclaimer_accepted'))
                        required
                        aria-required="true"
                    />
                    <span class="text-sm leading-relaxed">
                        Declaro haber leído y comprendido el aviso sobre protección de datos personales
                        (Ley N° 21.719) y el carácter auditado de este módulo.
                    </span>
                </label>
            </x-ui.field>

            <x-ui.field
                label="Contraseña institucional"
                for="gate_password"
                :error="$errors->first('password')"
                hint="Estimado/a {{ auth()->user()->display_name }}: confirme su identidad ingresando la contraseña de su cuenta en Acalis Pharma."
                required
            >
                <x-ui.input
                    id="gate_password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    autofocus
                    aria-required="true"
                    aria-describedby="gate-password-help"
                    placeholder="Ingrese su contraseña"
                />
            </x-ui.field>
            <p id="gate-password-help" class="text-xs text-base-content/55 -mt-2">
                Por su seguridad y la de los residentes, no podrá acceder a fichas clínicas sin esta verificación.
                Si olvidó su contraseña, cierre sesión y utilice la opción de recuperación.
            </p>

            @if ($errors->any())
                <div class="alert alert-error text-sm" role="alert" aria-live="assertive">
                    <ul class="list-disc ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="vx-resident-gate-actions">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Cancelar y volver</a>
                <button type="submit" class="btn btn-primary">
                    Confirmar e ingresar
                </button>
            </div>
        </form>

        <footer class="vx-resident-gate-footer">
            <p class="text-xs text-base-content/45">
                Acalis Pharma · Residencias de larga estadía · Cumplimiento Ley N° 21.719
            </p>
        </footer>
    </div>
</div>
@endsection
