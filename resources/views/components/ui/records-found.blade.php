@props([
    'count' => null,
    'items' => null,
])

@php
    $total = $count;

    if ($total === null && $items !== null) {
        $total = method_exists($items, 'total')
            ? $items->total()
            : (is_countable($items) ? count($items) : 0);
    }

    $total = (int) ($total ?? 0);
@endphp

<div {{ $attributes->merge(['class' => 'flex justify-end']) }}>
    <p class="text-sm font-bold text-base-content">
        Registros encontrados:
        <span class="font-bold tabular-nums text-red-500">{{ number_format($total, 0, ',', '.') }}</span>
    </p>
</div>
