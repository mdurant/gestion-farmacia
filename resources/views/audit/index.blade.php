@extends('layouts.app')

@section('title', 'Auditoría')
@section('page-title', 'Auditoría de usuarios')
@section('page-subtitle', 'Trail audit completo · cumplimiento Ley de derechos del paciente')

@section('content')
<div class="space-y-6">
    <div class="filter-toolbar">
        <form method="GET" class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
            <x-ui.field label="Acción" for="action">
                <x-ui.select id="action" name="action">
                    <option value="">Todas</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action->value }}" @selected(request('action') === $action->value)>
                            {{ $action->label() }}
                        </option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Usuario afectado (ID)" for="row_id">
                <x-ui.input id="row_id" type="number" name="row_id" value="{{ request('row_id') }}" />
            </x-ui.field>

            <x-ui.field label="Autor del cambio" for="user_id">
                <x-ui.select id="user_id" name="user_id">
                    <option value="">Todos</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Desde" for="from">
                <x-ui.input id="from" type="date" name="from" value="{{ request('from') }}" />
            </x-ui.field>

            <x-ui.field label="Hasta" for="to">
                <x-ui.input id="to" type="date" name="to" value="{{ request('to') }}" />
            </x-ui.field>

            <div class="flex items-end gap-2 md:col-span-4 xl:col-span-6">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="{{ route('audit.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            </div>
        </form>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr class="text-base-content/60">
                        <th>Fecha</th>
                        <th>Acción</th>
                        <th>Registro</th>
                        <th>Autor</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap text-sm">
                                {{ $log->created_at?->timezone('America/Santiago')->format('d/m/Y H:i:s') }}
                            </td>
                            <td><span class="badge badge-neutral badge-outline badge-sm">{{ $log->action->label() }}</span></td>
                            <td class="text-sm">#{{ $log->row_id }}</td>
                            <td class="text-sm">{{ $log->user?->display_name ?? 'Sistema' }}</td>
                            <td>
                                <details class="text-xs">
                                    <summary class="cursor-pointer link link-primary">Ver cambios</summary>
                                    <div class="mt-2 grid gap-2 md:grid-cols-2">
                                        @if ($log->old_values)
                                            <pre class="overflow-x-auto rounded-lg bg-base-200 p-2">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @endif
                                        @if ($log->new_values)
                                            <pre class="overflow-x-auto rounded-lg bg-base-200 p-2">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @endif
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-base-content/50">No hay registros de auditoría</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="border-t border-base-300 px-6 py-4">{{ $logs->links() }}</div>
        @endif
    </x-ui.card>
</div>
@endsection
