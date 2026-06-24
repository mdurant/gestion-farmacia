@extends('layouts.app')

@section('title', 'Editar residente')
@section('page-title', 'Editar residente')
@section('page-subtitle', $resident->full_name)

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('residents.show', $resident) }}" class="btn btn-ghost btn-sm mb-4">← {{ $resident->full_name }}</a>
    <x-ui.card>
        <form method="POST" action="{{ route('residents.update', $resident) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('residents.partials.form')
            <div class="form-actions pt-4">
                <button type="submit" class="btn btn-primary">Actualizar residente</button>
                <a href="{{ route('residents.show', $resident) }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
