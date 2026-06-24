@extends('layouts.app')

@section('title', $resident->full_name)
@section('page-title', $resident->full_name)
@section('page-subtitle', 'Ficha clínica · trazabilidad de medicación')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('residents.index') }}" class="btn btn-ghost btn-sm">← Residentes</a>
        @can('update', $resident)
            <a href="{{ route('residents.edit', $resident) }}" class="btn btn-sm btn-outline">Editar ficha</a>
        @endcan
        @can('create', \App\Models\InventoryMovement::class)
            @if ($resident->is_active)
                <a href="{{ route('inventory.movements.administration.create', ['resident_id' => $resident->id]) }}"
                   class="btn btn-primary btn-sm">+ Registrar administración</a>
            @endif
        @endcan
    </div>

    <div class="grid gap-4 sm:grid-cols-4">
        <x-ui.stat-card label="Tratamientos activos" :value="$stats['active_treatments']" color="secondary" icon="box" />
        <x-ui.stat-card label="Administraciones" :value="$stats['total_administrations']" color="primary" icon="activity" />
        <x-ui.stat-card label="Última administración"
                        :value="$stats['last_administration'] ? $stats['last_administration']->timezone('America/Santiago')->format('d/m/Y') : '—'"
                        color="info" icon="chart" />
        <x-ui.stat-card label="Estado" :value="$resident->is_active ? 'Activo' : 'Inactivo'"
                        :color="$resident->is_active ? 'success' : 'warning'" icon="users" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Datos personales y clínicos">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-base-content/50">RUT</dt><dd class="font-mono font-medium">{{ $resident->rut }}</dd></div>
                <div><dt class="text-base-content/50">Previsión</dt><dd>{{ $resident->healthInsurance?->name ?? '—' }}</dd></div>
                <div><dt class="text-base-content/50">Nacimiento</dt><dd>{{ $resident->birth_date?->format('d/m/Y') ?? '—' }} @if($resident->age) ({{ $resident->age }} años) @endif</dd></div>
                <div><dt class="text-base-content/50">Ingreso</dt><dd>{{ $resident->admission_date?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt class="text-base-content/50">Habitación</dt><dd>{{ $resident->room_number ?? '—' }}</dd></div>
                <div><dt class="text-base-content/50">Centro de costo</dt><dd>{{ $resident->costCenter?->name ?? '—' }}</dd></div>
                <div><dt class="text-base-content/50">Alergias</dt><dd>{{ $resident->allergies ?: '—' }}</dd></div>
                <div><dt class="text-base-content/50">Servicio rescate</dt><dd class="whitespace-pre-wrap">{{ $resident->rescue_service ?: '—' }}</dd></div>
                <div><dt class="text-base-content/50">Diagnóstico</dt><dd class="whitespace-pre-wrap">{{ $resident->diagnosis ?: '—' }}</dd></div>
                <div><dt class="text-base-content/50">Contacto emergencia</dt><dd>{{ $resident->emergency_contact_name ?? '—' }}</dd></div>
                <div><dt class="text-base-content/50">Teléfono emergencia</dt><dd>{{ $resident->emergency_contact_phone ?? '—' }}</dd></div>
                @if ($resident->medical_notes)
                    <div><dt class="text-base-content/50">Notas adicionales</dt><dd class="whitespace-pre-wrap">{{ $resident->medical_notes }}</dd></div>
                @endif
            </dl>
        </x-ui.card>

        <x-ui.card title="Trazabilidad">
            <p class="text-sm text-base-content/70 mb-4">
                Cada administración queda vinculada al fármaco, lote, profesional que la registró y número de receta médica.
            </p>
            <ul class="space-y-2 text-sm">
                <li class="flex items-center gap-2"><span class="badge badge-primary badge-outline badge-sm">1</span> Fármaco y lote administrado</li>
                <li class="flex items-center gap-2"><span class="badge badge-primary badge-outline badge-sm">2</span> Profesional responsable (usuario del sistema)</li>
                <li class="flex items-center gap-2"><span class="badge badge-primary badge-outline badge-sm">3</span> Receta / prescripción médica</li>
                <li class="flex items-center gap-2"><span class="badge badge-primary badge-outline badge-sm">4</span> Bodega y centro de costo de origen</li>
            </ul>
        </x-ui.card>
    </div>

    <x-ui.card title="Plan de tratamiento (medicamentos)">
        <p class="mb-4 text-sm text-base-content/60">
            Esquema cargado desde la base clínica: medicamento, presentación, dosis diaria/mensual, horario y tipo de tratamiento.
        </p>
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Medicamento</th>
                        <th>Presentación</th>
                        <th>Dosis/día</th>
                        <th>Dosis/mes</th>
                        <th>Horario</th>
                        <th>Tipo</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($resident->treatments->where('is_active', true) as $treatment)
                        <tr>
                            <td class="font-medium">{{ $treatment->drug?->name }}</td>
                            <td>{{ $treatment->presentation?->name ?? $treatment->drug?->presentation ?? '—' }}</td>
                            <td>{{ number_format((float) $treatment->daily_dose, 2, ',', '.') }}</td>
                            <td>{{ number_format((float) $treatment->monthly_dose, 2, ',', '.') }}</td>
                            <td>{{ $treatment->schedule_time ? substr((string) $treatment->schedule_time, 0, 5) : '—' }}</td>
                            <td><span class="badge badge-outline badge-sm">{{ $treatment->treatment_type?->label() }}</span></td>
                            <td class="max-w-xs truncate text-xs">{{ $treatment->observations ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-base-content/50">Sin tratamientos activos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.card title="Historial de administraciones">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Fecha</th>
                        <th>Fármaco</th>
                        <th>Lote</th>
                        <th>Cant.</th>
                        <th>Receta</th>
                        <th>Profesional</th>
                        <th>Bodega</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($administrations as $movement)
                        <tr>
                            <td class="whitespace-nowrap">{{ $movement->movement_at?->timezone('America/Santiago')->format('d/m/Y H:i') }}</td>
                            <td class="font-medium">
                                <a href="{{ route('inventory.drugs.show', $movement->drug) }}" class="link link-primary">
                                    {{ $movement->drug?->name }}
                                </a>
                            </td>
                            <td class="font-mono text-xs">{{ $movement->batch?->batch_number }}</td>
                            <td>{{ $movement->quantity }}</td>
                            <td class="font-mono text-xs">{{ $movement->prescription_id ?? '—' }}</td>
                            <td>{{ $movement->user?->display_name }}</td>
                            <td class="text-sm">{{ $movement->pharmacy?->name }}</td>
                            <td class="text-right">
                                <a href="{{ route('inventory.movements.index', ['movement_type' => 'salida_administracion', 'search' => $movement->drug?->code]) }}"
                                   class="btn btn-xs btn-ghost">Kardex</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-base-content/50">
                                Sin administraciones registradas.
                                @can('create', \App\Models\InventoryMovement::class)
                                    @if ($resident->is_active)
                                        <a href="{{ route('inventory.movements.administration.create', ['resident_id' => $resident->id]) }}"
                                           class="link link-primary">Registrar primera administración</a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($administrations->hasPages())
            <div class="border-t border-base-300 px-6 py-4">{{ $administrations->links() }}</div>
        @endif
    </x-ui.card>

    @can('delete', $resident)
        <x-ui.card title="Zona de riesgo">
            <form method="POST" action="{{ route('residents.destroy', $resident) }}"
                  onsubmit="return confirm('¿Dar de baja este residente? La trazabilidad histórica se conservará.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error btn-outline btn-sm">Dar de baja residente</button>
            </form>
        </x-ui.card>
    @endcan
</div>
@endsection
