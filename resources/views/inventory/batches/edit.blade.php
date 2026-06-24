@extends('layouts.app')

@section('title', 'Editar lote')
@section('page-title', 'Editar lote')
@section('page-subtitle', $batch->batch_number . ' · ' . ($batch->drug?->name ?? ''))

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('inventory.batches.show', $batch) }}" class="btn btn-ghost btn-sm mb-4">← Lote {{ $batch->batch_number }}</a>
    <x-ui.card>
        <form method="POST" action="{{ route('inventory.batches.update', $batch) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('inventory.batches.partials.form')
            <div class="form-actions pt-4">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('inventory.batches.show', $batch) }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>

    @if ($batch->availableQuantity() === 0 && ! $batch->movements()->exists())
        <x-ui.card title="Zona de riesgo" class="mt-6">
            <form method="POST" action="{{ route('inventory.batches.destroy', $batch) }}"
                  onsubmit="return confirm('¿Dar de baja este lote?');">
                @csrf
                @method('DELETE')
                @error('delete')
                    <div class="alert alert-error mb-4 text-sm">{{ $message }}</div>
                @enderror
                <button type="submit" class="btn btn-error btn-outline btn-sm">Dar de baja lote</button>
            </form>
        </x-ui.card>
    @endif
</div>
@endsection
