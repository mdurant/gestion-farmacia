@props(['type' => 'text'])

@if ($type === 'date')
    <input
        type="text"
        {{ $attributes->merge([
            'class' => 'input vx-control w-full vx-date-field',
            'data-vx-datepicker' => 'true',
            'autocomplete' => 'off',
            'placeholder' => 'dd/mm/aaaa',
            'inputmode' => 'numeric',
        ]) }}
    />
@else
    <input
        type="{{ $type }}"
        {{ $attributes->merge(['class' => 'input vx-control w-full']) }}
    />
@endif
