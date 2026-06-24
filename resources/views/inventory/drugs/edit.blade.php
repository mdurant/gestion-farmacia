@extends('layouts.app')

@section('title', 'Editar fármaco')
@section('page-title', $drug->name)
@section('page-subtitle', 'Actualizar datos del catálogo')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-4 flex gap-2">
        <a href="{{ route('inventory.drugs.show', $drug) }}" class="btn btn-ghost btn-sm">← Kardex</a>
    </div>
    <x-ui.card>
        <form method="POST" action="{{ route('inventory.drugs.update', $drug) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('inventory.drugs.partials.form', ['drug' => $drug])
            <div class="form-actions border-t border-base-300 pt-4">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
