@props(['variant' => 'header'])

@php
    $uploaded = site()->url($variant === 'footer' ? 'logo_footer' : 'logo') ?? ($variant === 'footer' ? site()->url('logo') : null);
    $name = site()->get('site_name');
@endphp

@if ($uploaded)
    <img src="{{ $uploaded }}" alt="{{ $name }}" {{ $attributes->merge(['class' => 'h-auto w-auto object-contain']) }}>
@else
    {{-- Default vector wordmark (inherits the site font). --}}
    <svg viewBox="0 0 206 66" role="img" aria-label="{{ $name }}" {{ $attributes }}>
        <text x="96" y="22" font-size="25" font-weight="400" letter-spacing="-0.5" fill="#1674DB">solution</text>
        <text x="0" y="57" font-size="42" font-weight="500" letter-spacing="-1" fill="#0755C8">net</text>
        <rect x="64" y="25" width="141" height="40" rx="3" fill="#0755C8" />
        <text x="68" y="57" font-size="42" font-weight="500" letter-spacing="-1" fill="#FFFFFF">mobile</text>
    </svg>
@endif
