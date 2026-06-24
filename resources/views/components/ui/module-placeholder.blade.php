@props([
    'title',
    'description',
    'icon' => 'box',
])

<x-ui.card>
    <div class="vx-module-placeholder">
        <div class="vx-module-placeholder-icon">
            @include('partials.nav-icon', ['name' => $icon])
        </div>
        <h2 class="text-xl font-semibold text-base-content">{{ $title }}</h2>
        <p class="mx-auto mt-2 max-w-md text-base-content/60">{{ $description }}</p>
        <div class="mt-6">
            <span class="badge badge-primary badge-outline">Próximamente</span>
        </div>
    </div>
</x-ui.card>
