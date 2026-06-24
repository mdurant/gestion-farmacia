@extends('layouts.app')

@section('title', 'Mi perfil')
@section('page-title', 'Mi perfil')
@section('page-subtitle', 'Actualice su información de acceso')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.card>
        <div class="mb-6 flex items-center gap-4 border-b border-base-300 pb-6">
            <x-ui.avatar :name="auth()->user()->display_name" size="xl" ring />
            <div>
                <h2 class="text-xl font-semibold">{{ auth()->user()->display_name }}</h2>
                <p class="text-sm text-base-content/60">{{ auth()->user()->email }}</p>
                <p class="mt-1 text-xs text-base-content/45">{{ auth()->user()->role?->label() ?? 'Personal' }}</p>
            </div>
        </div>
        @include('profile.partials.update-profile-information-form')
    </x-ui.card>

    <x-ui.card>
        @include('profile.partials.update-password-form')
    </x-ui.card>

    <x-ui.card>
        @include('profile.partials.delete-user-form')
    </x-ui.card>

    <x-access.access-audit-table
        :access-logs="$accessLogs"
        :export-excel-route="route('profile.access-log.export', ['format' => 'csv'])"
        :export-pdf-route="route('profile.access-log.export', ['format' => 'pdf'])"
    />
</div>
@endsection
