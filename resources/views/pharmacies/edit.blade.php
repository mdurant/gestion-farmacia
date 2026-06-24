@extends('layouts.app')

@section('title', 'Editar bodega')
@section('page-title', 'Editar bodega')
@section('page-subtitle', $pharmacy->name)

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('pharmacies.show', $pharmacy) }}" class="btn btn-ghost btn-sm mb-4">← {{ $pharmacy->name }}</a>
    <x-ui.card>
        <form method="POST" action="{{ route('pharmacies.update', $pharmacy) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('pharmacies.partials.form')
            <div class="form-actions border-t border-base-300 pt-4">
                <button type="submit" class="btn btn-primary">Actualizar bodega</button>
                <a href="{{ route('pharmacies.show', $pharmacy) }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
