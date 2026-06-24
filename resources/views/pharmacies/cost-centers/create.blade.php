@extends('layouts.app')

@section('title', 'Nuevo centro de costo')
@section('page-title', 'Registrar centro de costo')
@section('page-subtitle', 'Piso, pabellón o unidad clínica')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('pharmacies.cost-centers.index') }}" class="btn btn-ghost btn-sm mb-4">← Centros de costo</a>
    <x-ui.card>
        <form method="POST" action="{{ route('pharmacies.cost-centers.store') }}" class="space-y-6">
            @include('pharmacies.cost-centers.partials.form')
            <div class="form-actions border-t border-base-300 pt-4">
                <button type="submit" class="btn btn-primary">Guardar centro</button>
                <a href="{{ route('pharmacies.cost-centers.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
