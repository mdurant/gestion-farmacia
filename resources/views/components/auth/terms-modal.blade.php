@props([
    'termsVersion' => config('acalis.terms.version', '1.0.0'),
])

<dialog id="terms-dialog" class="modal modal-bottom sm:modal-middle" aria-labelledby="terms-dialog-title">
    <div class="modal-box max-w-2xl p-0">
        <header class="border-b border-base-300 bg-primary/5 px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-primary">Versión {{ $termsVersion }}</p>
            <h3 id="terms-dialog-title" class="mt-1 text-xl font-bold text-base-content">
                Términos de uso e información de la plataforma
            </h3>
            <p class="mt-2 text-sm text-base-content/70">
                Lea este resumen antes de aceptar el uso de información en Acalis Pharma.
            </p>
        </header>

        <div class="max-h-[min(60vh,32rem)] space-y-5 overflow-y-auto px-6 py-5 text-sm leading-relaxed text-base-content/85">
            <section>
                <h4 class="mb-2 flex items-center gap-2 font-semibold text-base-content">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-info/15 text-info">1</span>
                    Tratamiento de datos personales
                </h4>
                <p>
                    Acalis Pharma procesa datos personales y clínicos de residentes, personal de salud y operaciones
                    farmacéuticas conforme a la <strong>Ley N° 21.719</strong> sobre Protección de Datos Personales.
                    Los identificadores sensibles (RUT, nombres, datos clínicos) se almacenan cifrados.
                </p>
                <ul class="mt-2 list-disc space-y-1 ps-5">
                    <li>Solo el personal autorizado puede acceder a la información según su rol.</li>
                    <li>Cada consulta, alta, edición o eliminación queda registrada en auditoría.</li>
                    <li>El uso debe limitarse a fines clínicos, administrativos y de trazabilidad farmacéutica.</li>
                </ul>
            </section>

            <section>
                <h4 class="mb-2 flex items-center gap-2 font-semibold text-base-content">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-success/15 text-success">2</span>
                    ¿Qué es Acalis Pharma?
                </h4>
                <p>
                    Es una plataforma web institucional para residencias de larga estadía en Chile. Centraliza la
                    gestión farmacéutica, el inventario por bodega, la trazabilidad de fármacos controlados y el
                    registro clínico asociado, con controles de acceso por rol y trazabilidad completa.
                </p>
            </section>

            <section>
                <h4 class="mb-2 flex items-center gap-2 font-semibold text-base-content">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-warning/15 text-warning">3</span>
                    Módulos principales
                </h4>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-base-300 bg-base-200/40 p-3">
                        <p class="font-medium text-base-content">Dashboard</p>
                        <p class="mt-1 text-xs text-base-content/65">Indicadores operativos y alertas de stock.</p>
                    </div>
                    <div class="rounded-xl border border-base-300 bg-base-200/40 p-3">
                        <p class="font-medium text-base-content">Inventario y bodegas</p>
                        <p class="mt-1 text-xs text-base-content/65">Stock, lotes, movimientos y kardex.</p>
                    </div>
                    <div class="rounded-xl border border-base-300 bg-base-200/40 p-3">
                        <p class="font-medium text-base-content">Residentes</p>
                        <p class="mt-1 text-xs text-base-content/65">Datos clínicos protegidos con verificación adicional.</p>
                    </div>
                    <div class="rounded-xl border border-base-300 bg-base-200/40 p-3">
                        <p class="font-medium text-base-content">Auditoría</p>
                        <p class="mt-1 text-xs text-base-content/65">Historial de acciones por usuario y registro.</p>
                    </div>
                </div>
            </section>

            <section>
                <h4 class="mb-2 flex items-center gap-2 font-semibold text-base-content">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-secondary/15 text-secondary">4</span>
                    Objetivo del proyecto
                </h4>
                <p>
                    Reducir errores de medicación, mejorar la trazabilidad de fármacos controlados y garantizar el
                    cumplimiento normativo en residencias. La plataforma busca apoyar decisiones clínicas seguras con
                    información confiable, actualizada y auditada.
                </p>
            </section>

            <div class="alert alert-info text-sm">
                <span>
                    Al marcar la casilla de aceptación confirma que ha leído esta información (versión
                    <strong>{{ $termsVersion }}</strong>) y autoriza el uso de la plataforma conforme a las políticas
                    institucionales de su organización.
                </span>
            </div>
        </div>

        <div class="modal-action border-t border-base-300 px-6 py-4">
            <form method="dialog">
                <button type="submit" class="btn btn-primary">Entendido</button>
            </form>
        </div>
    </div>

    <form method="dialog" class="modal-backdrop">
        <button type="submit" aria-label="Cerrar términos">cerrar</button>
    </form>
</dialog>
