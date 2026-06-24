@extends('layouts.app')

@section('title', 'Nuevo residente')
@section('page-title', 'Registrar residente')
@section('page-subtitle', 'Alta con datos clínicos cifrados')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('residents.index') }}" class="btn btn-ghost btn-sm mb-4">← Residentes</a>
    <x-ui.card>
        <form method="POST" action="{{ route('residents.store') }}" class="space-y-6">
            @include('residents.partials.form')
            <div class="form-actions pt-4">
                <button type="submit" class="btn btn-primary">Guardar residente</button>
                <a href="{{ route('residents.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
