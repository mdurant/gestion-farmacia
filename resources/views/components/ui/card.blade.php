@props(['title' => null])

<div {{ $attributes->merge(['class' => 'vx-card']) }}>
    <div class="vx-card-body">
        @if($title)
            <h2 class="vx-card-title mb-4">{{ $title }}</h2>
        @endif
        {{ $slot }}
    </div>
</div>
