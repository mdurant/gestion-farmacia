@extends('layouts.app')

@section('title', 'Nueva bodega')
@section('page-title', 'Registrar bodega')
@section('page-subtitle', 'Central, botiquín o módulo de emergencia')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('pharmacies.index') }}" class="btn btn-ghost btn-sm mb-4">← Bodegas</a>
    <x-ui.card>
        <form method="POST" action="{{ route('pharmacies.store') }}" class="space-y-6">
            @include('pharmacies.partials.form')
            <div class="form-actions border-t border-base-300 pt-4">
                <button type="submit" class="btn btn-primary">Guardar bodega</button>
                <a href="{{ route('pharmacies.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
