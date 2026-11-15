@props(['name', 'fallback' => 'sparkles'])

@php
    $iconAttributes = array_merge(
        ['aria-hidden' => 'true', 'stroke-width' => 1.75],
        $attributes->except(['class'])->getAttributes(),
    );

    try {
        $svg = svg('lucide-'.$name, $attributes->get('class', ''), $iconAttributes);
    } catch (\BladeUI\Icons\Exceptions\SvgNotFound) {
        $svg = svg('lucide-'.$fallback, $attributes->get('class', ''), $iconAttributes);
    }
@endphp

{{ $svg }}
