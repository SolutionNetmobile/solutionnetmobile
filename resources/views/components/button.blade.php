@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconLeft' => null,
    'type' => 'button',
])

@php
    $base = 'group inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60';

    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-[0.95rem]',
        'lg' => 'px-7 py-3.5 text-base',
    ];

    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-sm shadow-brand-600/30 hover:bg-brand-700 hover:shadow-md hover:shadow-brand-600/30 active:bg-brand-800 focus-visible:outline-brand-600',
        'outline' => 'border-2 border-brand-600 bg-white text-brand-600 hover:bg-brand-50 active:bg-brand-100 focus-visible:outline-brand-600',
        'white' => 'bg-white text-brand-600 shadow-sm hover:bg-brand-50 active:bg-brand-100 focus-visible:outline-white',
    ];

    $classes = implode(' ', [$base, $sizes[$size] ?? $sizes['md'], $variants[$variant] ?? $variants['primary']]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($iconLeft)<x-site.icon :name="$iconLeft" class="size-4.5 shrink-0" />@endif
        <span>{{ $slot }}</span>
        @if ($icon)<x-site.icon :name="$icon" class="size-4 shrink-0 transition-transform duration-200 group-hover:translate-x-0.5" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($iconLeft)<x-site.icon :name="$iconLeft" class="size-4.5 shrink-0" />@endif
        <span>{{ $slot }}</span>
        @if ($icon)<x-site.icon :name="$icon" class="size-4 shrink-0 transition-transform duration-200 group-hover:translate-x-0.5" />@endif
    </button>
@endif
