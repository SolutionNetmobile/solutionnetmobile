@props([
    'name',
    'label',
    'required' => false,
    'hint' => null,
])

<div {{ $attributes->class('flex flex-col') }}>
    <label for="field-{{ $name }}" class="form-label">
        {{ $label }}
        @if ($required)
            <span class="text-brand-600" aria-hidden="true">*</span>
        @else
            <span class="font-normal text-muted">(facultatif)</span>
        @endif
    </label>

    {{ $slot }}

    @if ($hint)
        <p id="hint-{{ $name }}" class="mt-1.5 text-xs text-muted">{{ $hint }}</p>
    @endif

    <p id="error-{{ $name }}" class="form-error" role="alert" x-show="errors['{{ $name }}']" x-text="errors['{{ $name }}']" @if (! $errors->has($name)) x-cloak @endif>
        {{ $errors->first($name) }}
    </p>
</div>
