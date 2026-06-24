@extends('layouts.app')

@section('title', $costCenter->name)
@section('page-title', $costCenter->name)
@section('page-subtitle', $costCenter->code)

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('pharmacies.cost-centers.index') }}" class="btn btn-ghost btn-sm">← Centros de costo</a>
        @can('update', $costCenter)
            <a href="{{ route('pharmacies.cost-centers.edit', $costCenter) }}" class="btn btn-sm btn-outline">Editar</a>
        @endcan
        @can('create', \App\Models\Pharmacy::class)
            <a href="{{ route('pharmacies.create') }}" class="btn btn-sm btn-primary btn-outline">+ Asociar bodega</a>
        @endcan
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Datos generales">
            <dl class="grid gap-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Código</dt>
                    <dd class="font-mono font-medium">{{ $costCenter->code }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Piso</dt>
                    <dd>{{ $costCenter->floor ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Pabellón</dt>
                    <dd>{{ $costCenter->pavilion ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-base-300 pb-2">
                    <dt class="text-base-content/60">Estado</dt>
                    <dd>
                        @if ($costCenter->is_active)
                            <span class="badge badge-success badge-outline badge-sm">Activo</span>
                        @else
                            <span class="badge badge-warning badge-outline badge-sm">Inactivo</span>
                        @endif
                    </dd>
                </div>
                @if ($costCenter->description)
                    <div>
                        <dt class="mb-1 text-base-content/60">Descripción</dt>
                        <dd>{{ $costCenter->description }}</dd>
                    </div>
                @endif
            </dl>
        </x-ui.card>

        <x-ui.card title="Bodegas asociadas">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="text-base-content/60">
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($costCenter->pharmacies as $pharmacy)
                            <tr>
                                <td class="font-mono text-xs">{{ $pharmacy->code }}</td>
                                <td>{{ $pharmacy->name }}</td>
                                <td><span class="badge badge-neutral badge-outline badge-sm">{{ $pharmacy->type->label() }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('pharmacies.show', $pharmacy) }}" class="btn btn-xs btn-ghost">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-base-content/50">Sin bodegas asociadas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    @can('delete', $costCenter)
        <x-ui.card title="Zona de riesgo">
            <form method="POST" action="{{ route('pharmacies.cost-centers.destroy', $costCenter) }}"
                  onsubmit="return confirm('¿Dar de baja este centro de costo?');">
                @csrf
                @method('DELETE')
                @error('delete')
                    <div class="alert alert-error mb-4 text-sm">{{ $message }}</div>
                @enderror
                <button type="submit" class="btn btn-error btn-outline btn-sm">Dar de baja centro</button>
            </form>
        </x-ui.card>
    @endcan
</div>
@endsection
