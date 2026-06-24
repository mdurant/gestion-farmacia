@extends('layouts.app')

@section('title', 'Editar centro de costo')
@section('page-title', 'Editar centro de costo')
@section('page-subtitle', $costCenter->name)

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('pharmacies.cost-centers.show', $costCenter) }}" class="btn btn-ghost btn-sm mb-4">← {{ $costCenter->name }}</a>
    <x-ui.card>
        <form method="POST" action="{{ route('pharmacies.cost-centers.update', $costCenter) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('pharmacies.cost-centers.partials.form')
            <div class="form-actions border-t border-base-300 pt-4">
                <button type="submit" class="btn btn-primary">Actualizar centro</button>
                <a href="{{ route('pharmacies.cost-centers.show', $costCenter) }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
