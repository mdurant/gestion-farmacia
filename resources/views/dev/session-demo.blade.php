@extends('layouts.app')

@section('title', 'Demo sesiones')
@section('page-title', 'Demo — Sesión única y timeout')
@section('page-subtitle', 'Solo entorno local · herramientas de prueba')

@section('content')
<div class="space-y-6">
    @if (session('status'))
        <div class="alert alert-success shadow-sm">{{ session('status') }}</div>
    @endif

    <div class="alert alert-info text-sm leading-relaxed">
        <div>
            <p class="font-semibold">Sobre el mensaje en consola del navegador</p>
            <p class="mt-2">
                Si ve <code class="text-xs">Unchecked runtime.lastError: The message port closed before a response was received</code>,
                proviene casi siempre de una <strong>extensión del navegador</strong> (gestor de contraseñas, bloqueador de anuncios, Cursor, etc.),
                no de Acalis Pharma. Para una prueba limpia use ventana de incógnito o desactive extensiones.
            </p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-ui.card title="Estado actual">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Usuario</dt>
                    <dd class="font-medium">{{ $user->display_name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Sesión única activa</dt>
                    <dd>
                        <span class="badge {{ $singleDeviceEnabled ? 'badge-success' : 'badge-ghost' }} badge-sm">
                            {{ $singleDeviceEnabled ? 'Sí' : 'No' }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Token en este navegador</dt>
                    <dd class="max-w-[14rem] truncate font-mono text-xs" title="{{ $deviceToken }}">{{ $deviceToken ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Token vigente en servidor</dt>
                    <dd class="max-w-[14rem] truncate font-mono text-xs" title="{{ $serverToken }}">{{ $serverToken ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-base-content/60">¿Sesión válida aquí?</dt>
                    <dd>
                        <span class="badge {{ $tokensMatch ? 'badge-success' : 'badge-error' }} badge-sm">
                            {{ $tokensMatch ? 'Vigente' : 'Reemplazada' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Política configurada">
            <ul class="space-y-2 text-sm text-base-content/80">
                <li>• Inactividad: <strong>{{ $idleMinutes }} minutos</strong> → modal con <strong>{{ $warningSeconds }} s</strong> para continuar.</li>
                <li>• Duración máxima: <strong>{{ config('acalis.session.absolute_lifetime_minutes', 60) }} minutos</strong>.</li>
                <li>• Un solo dispositivo: al ingresar en otro equipo se pide confirmación y se cierra la sesión anterior.</li>
                <li>• Sondeo del portal: cada <strong>10 segundos</strong> (o al volver a la pestaña).</li>
            </ul>
        </x-ui.card>
    </div>

    <x-ui.card title="Pruebas rápidas">
        <div class="grid gap-3 md:grid-cols-2">
            <form method="POST" action="{{ route('dev.session-demo.simulate') }}">
                @csrf
                <button type="submit" class="btn btn-warning w-full">
                    Simular ingreso desde otro equipo
                </button>
                <p class="mt-2 text-xs text-base-content/55">
                    Cambia el token en servidor sin tocar este navegador. Debe aparecer el modal
                    «Cerrar sesión en este dispositivo» en el portal (máx. 10 s).
                </p>
            </form>

            <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
                <p class="text-sm font-medium">Prueba con dos equipos reales</p>
                <ol class="mt-2 list-decimal space-y-1 ps-4 text-xs text-base-content/70">
                    <li>Equipo A: login con <kbd class="kbd kbd-xs">{{ \App\Support\DemoAccounts::loginPanel()[0]['email'] ?? 'acalisnotificaciones+admin@gmail.com' }}</kbd> / <kbd class="kbd kbd-xs">password</kbd></li>
                    <li>Equipo B: mismo usuario → modal «Cerrar sesión en otros dispositivos»</li>
                    <li>Confirmar en B → en A aparece modal de sesión cerrada</li>
                </ol>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card title="Respuesta en vivo — /sesion/estado">
        <pre id="session-demo-output" class="max-h-64 overflow-auto rounded-lg bg-base-200 p-3 text-xs">Consultando…</pre>
        <button type="button" class="btn btn-ghost btn-sm mt-3" id="session-demo-refresh">Actualizar ahora</button>
    </x-ui.card>
</div>

<script>
    (function () {
        const output = document.getElementById('session-demo-output');
        const refreshBtn = document.getElementById('session-demo-refresh');
        const statusUrl = @json(route('dev.session-demo.status'));

        async function refreshStatus() {
            try {
                const response = await fetch(statusUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const text = await response.text();
                output.textContent = 'HTTP ' + response.status + '\n' + text;
            } catch (error) {
                output.textContent = 'Error: ' + error.message;
            }
        }

        refreshBtn?.addEventListener('click', refreshStatus);
        refreshStatus();
        setInterval(refreshStatus, 5000);
    })();
</script>
@endsection
