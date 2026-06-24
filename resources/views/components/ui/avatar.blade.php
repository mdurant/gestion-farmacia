@props([
    'name',
    'size' => 'md',
    'src' => null,
    'ring' => false,
    'portal' => false,
])

@php
    $initials = \App\Support\Avatar::initials($name);
    $colors = \App\Support\Avatar::colors($name);
    $sizeClass = match ($size) {
        'xs' => 'w-7 h-7 text-[0.625rem]',
        'sm' => 'w-9 h-9 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-14 h-14 text-base',
        'xl' => 'w-20 h-20 text-xl',
        default => 'w-10 h-10 text-sm',
    };
    $portalRing = $ring || $portal;
@endphp

<div {{ $attributes->class(['avatar', 'avatar-placeholder' => ! $src]) }}>
    <div @class([
        'rounded-full font-semibold flex items-center justify-center overflow-hidden',
        $sizeClass,
        'vx-avatar-portal-ring' => $portalRing,
    ]) @style($src ? [] : [
        'background-color' => $colors['background'],
        'color' => $colors['color'],
    ])>
        @if ($src)
            <img src="{{ $src }}" alt="{{ $name }}" class="h-full w-full object-cover" />
        @else
            <span>{{ $initials }}</span>
        @endif
    </div>
</div>
