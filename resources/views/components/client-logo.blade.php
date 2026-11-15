@props(['client'])

@php
    $logo = $client->logo_url
        ? '<img src="'.e($client->logo_url).'" alt="'.e($client->name).'" loading="lazy" decoding="async" class="h-11 w-auto max-w-[10rem] object-contain">'
        : '<span class="text-sm font-semibold tracking-wide text-muted uppercase">'.e($client->name).'</span>';
@endphp

<li {{ $attributes->class('flex h-14 items-center justify-center opacity-70 grayscale transition duration-300 hover:opacity-100 hover:grayscale-0') }}>
    @if ($client->website_url)
        <a href="{{ $client->website_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center rounded-md" title="{{ $client->name }} (nouvel onglet)">
            {!! $logo !!}
        </a>
    @else
        {!! $logo !!}
    @endif
</li>
