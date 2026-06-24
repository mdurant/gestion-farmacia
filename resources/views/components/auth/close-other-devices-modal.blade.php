@props([
    'open' => session('confirm_close_other_devices', false),
    'activeSession' => session('active_session_info'),
])

<dialog
    id="close-other-devices-dialog"
    class="modal modal-middle"
    @if ($open) open @endif
    aria-labelledby="close-other-devices-title"
    aria-describedby="close-other-devices-description"
>
    <div class="modal-box max-w-md border border-info/30">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-info/15 text-2xl" aria-hidden="true">📱</div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-info">Sesión activa detectada</p>
                <h3 id="close-other-devices-title" class="text-lg font-bold">Cerrar sesión en otros dispositivos</h3>
            </div>
        </div>

        <p id="close-other-devices-description" class="text-sm leading-relaxed text-base-content/80">
            Ya existe una sesión abierta con su cuenta en otro navegador o equipo.
            Por seguridad, solo puede permanecer conectado en <strong>un dispositivo a la vez</strong>.
        </p>

        @if (is_array($activeSession))
            <dl class="mt-4 space-y-3 rounded-xl border border-base-300 bg-base-200/40 p-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/50">Navegador</dt>
                    <dd class="mt-1 font-medium">{{ $activeSession['browser'] ?? 'Desconocido' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/50">Fecha y hora de conexión</dt>
                    <dd class="mt-1 font-medium">
                        @if (! empty($activeSession['connected_at']))
                            {{ \Illuminate\Support\Carbon::parse($activeSession['connected_at'])->timezone('America/Santiago')->format('d/m/Y H:i') }}
                        @else
                            No disponible
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/50">Ubicación geográfica</dt>
                    <dd class="mt-1 font-medium">{{ $activeSession['location'] ?? 'Ubicación no disponible' }}</dd>
                </div>
            </dl>
        @endif

        <p class="mt-4 text-sm text-base-content/75">
            Si continúa aquí, la otra sesión será cerrada automáticamente en todos los dispositivos.
        </p>

        <div class="modal-action mt-4 flex-col gap-2 sm:flex-row">
            <form method="POST" action="{{ route('login.confirm-other-devices') }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="btn btn-primary w-full">
                    Cerrar otras sesiones e ingresar
                </button>
            </form>
            <button
                type="button"
                class="btn btn-ghost w-full sm:w-auto"
                onclick="document.getElementById('close-other-devices-dialog').close();"
            >
                Cancelar
            </button>
        </div>
    </div>

    <form method="dialog" class="modal-backdrop">
        <button type="submit" aria-label="Cerrar aviso">cerrar</button>
    </form>
</dialog>
