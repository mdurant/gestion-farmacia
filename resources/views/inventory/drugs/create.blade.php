@extends('layouts.app')

@section('title', 'Nuevo fármaco')
@section('page-title', 'Registrar fármaco')
@section('page-subtitle', 'Alta en catálogo institucional')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('inventory.drugs.index') }}" class="btn btn-ghost btn-sm mb-4">← Catálogo</a>
    <x-ui.card>
        <form method="POST" action="{{ route('inventory.drugs.store') }}" class="space-y-6">
            @include('inventory.drugs.partials.form')
            <div class="form-actions border-t border-base-300 pt-4">
                <button type="submit" class="btn btn-primary">Guardar fármaco</button>
                <a href="{{ route('inventory.drugs.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
