@props([
    'accessLogs',
    'exportExcelRoute',
    'exportPdfRoute',
    'showUser' => false,
])

<x-ui.card title="Auditoría de acceso">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-base-content/60">
            Registro de conexiones y desconexiones al sistema.
        </p>
        <x-ui.export-buttons
            :excel-route="$exportExcelRoute"
            :pdf-route="$exportPdfRoute"
        />
    </div>

    <div class="overflow-x-auto">
        <table class="table table-zebra table-sm">
            <thead>
                <tr class="text-base-content/60">
                    <th>Conexión</th>
                    <th>Desconexión</th>
                    <th>Navegador</th>
                    <th>Ubicación</th>
                    <th>IP</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($accessLogs as $log)
                    <tr>
                        <td>{{ $log->connected_at?->timezone('America/Santiago')->format('d/m/Y H:i:s') }}</td>
                        <td>
                            @if ($log->disconnected_at)
                                {{ $log->disconnected_at->timezone('America/Santiago')->format('d/m/Y H:i:s') }}
                            @else
                                <span class="text-base-content/45">—</span>
                            @endif
                        </td>
                        <td>{{ $log->browser ?? '—' }}</td>
                        <td>{{ $log->location ?? '—' }}</td>
                        <td class="font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                        <td>
                            @if ($log->isActive())
                                <span class="badge badge-success badge-outline badge-sm">Activa</span>
                            @else
                                <span class="badge badge-neutral badge-outline badge-sm">
                                    {{ $log->disconnect_reason ?? 'Cerrada' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-base-content/50">
                            Sin registros de acceso
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($accessLogs->hasPages())
        <div class="border-t border-base-300 px-2 py-4">{{ $accessLogs->links() }}</div>
    @endif
</x-ui.card>
