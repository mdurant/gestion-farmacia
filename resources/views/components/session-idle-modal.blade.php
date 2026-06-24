@php
    $idleMinutes = (int) config('acalis.session.idle_minutes', 15);
    $warningSeconds = (int) config('acalis.session.warning_countdown_seconds', 60);
    $absoluteMinutes = (int) config('acalis.session.absolute_lifetime_minutes', 60);
@endphp

<div
    x-data="sessionGuard({
        idleMs: {{ $idleMinutes * 60 * 1000 }},
        warningMs: {{ $warningSeconds * 1000 }},
        pollMs: 10000,
        statusUrl: @js(route('session.status')),
        renewUrl: @js(route('session.renew')),
        logoutUrl: @js(route('logout')),
        loginUrl: @js(\App\Support\LoginUrl::to(['sesion' => 'reemplazada'])),
        csrfToken: @js(csrf_token()),
    })"
    x-cloak
>
    {{-- Modal: sesión reemplazada por otro dispositivo --}}
    <dialog
        class="modal modal-middle"
        :class="{ 'modal-open': showSuperseded }"
        aria-labelledby="session-superseded-title"
        aria-describedby="session-superseded-description"
        role="alertdialog"
    >
        <div class="modal-box max-w-md border border-error/30">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-error/15 text-2xl" aria-hidden="true">📱</div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-error">Sesión finalizada</p>
                    <h3 id="session-superseded-title" class="text-lg font-bold">Cerrar sesión en este dispositivo</h3>
                </div>
            </div>

            <p id="session-superseded-description" class="text-sm leading-relaxed text-base-content/80">
                Se inició sesión con su cuenta desde <strong>otro navegador o equipo</strong>.
                Por política de seguridad, esta sesión ya no es válida y debe volver a ingresar si desea continuar aquí.
            </p>

            <div class="modal-action mt-4">
                <button type="button" class="btn btn-primary w-full sm:w-auto" @click="goToLoginAfterSuperseded()">
                    Ir al inicio de sesión
                </button>
            </div>
        </div>
    </dialog>

    {{-- Modal: inactividad --}}
    <dialog
        class="modal modal-middle"
        :class="{ 'modal-open': showWarning }"
        aria-labelledby="session-idle-title"
        aria-describedby="session-idle-description"
        role="alertdialog"
    >
        <div class="modal-box max-w-md border border-warning/30">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-warning/15 text-2xl" aria-hidden="true">⏳</div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-warning">Sesión inactiva</p>
                    <h3 id="session-idle-title" class="text-lg font-bold">¿Desea continuar en el sistema?</h3>
                </div>
            </div>

            <p id="session-idle-description" class="text-sm leading-relaxed text-base-content/80">
                No detectamos actividad en los últimos {{ $idleMinutes }} minutos.
                Por seguridad, su sesión se cerrará automáticamente si no confirma continuar.
                La duración máxima de sesión es de {{ $absoluteMinutes }} minutos.
            </p>

            <div class="my-5 flex items-center justify-center">
                <div
                    class="radial-progress text-warning"
                    :style="`--value:${countdownPercent}; --size:5.5rem; --thickness:4px;`"
                    role="timer"
                    :aria-valuenow="countdownSeconds"
                    aria-valuemin="0"
                    :aria-valuemax="{{ $warningSeconds }}"
                >
                    <span class="text-lg font-bold tabular-nums" x-text="countdownSeconds"></span>
                </div>
            </div>

            <div class="modal-action mt-2 flex-col gap-2 sm:flex-row">
                <button type="button" class="btn btn-primary w-full sm:w-auto" @click="renewSession()">
                    Continuar sesión
                </button>
                <button type="button" class="btn btn-ghost w-full sm:w-auto" @click="logoutNow()">
                    Cerrar sesión ahora
                </button>
            </div>
        </div>
    </dialog>
</div>
