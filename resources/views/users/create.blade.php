@extends('layouts.app')

@section('title', 'Nuevo usuario')
@section('page-title', 'Alta de usuario')
@section('page-subtitle', 'Registrar personal con rol y accesos')

@section('content')
<div class="mx-auto max-w-3xl">
    <x-ui.card>
        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @include('users.partials.form', ['roles' => $roles])
            <div class="form-actions border-t border-base-300 pt-4">
                <button type="submit" class="btn btn-primary">Crear usuario</button>
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
