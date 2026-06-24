@props(['label', 'for' => null, 'error' => null, 'hint' => null, 'required' => false])

<div {{ $attributes->merge(['class' => 'vx-field flex w-full flex-col gap-2']) }}>
    <label @class(['vx-field-label']) @if($for) for="{{ $for }}" @endif>
        {{ $label }}
        @if($required)
            <span class="text-error" aria-hidden="true"> *</span>
        @endif
    </label>

    {{ $slot }}

    @if($error)
        <p class="vx-field-error" role="alert">{{ $error }}</p>
    @elseif($hint)
        <p class="vx-field-hint">{{ $hint }}</p>
    @endif
</div>
