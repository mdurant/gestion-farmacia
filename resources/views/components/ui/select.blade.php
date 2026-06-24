@props(['native' => false])

<select
    {{ $attributes->merge([
        'class' => 'select vx-control w-full',
        'data-vx-native' => $native ? 'true' : null,
    ]) }}
>
    {{ $slot }}
</select>
