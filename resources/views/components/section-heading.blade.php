@props([
    'kicker' => null,
    'title',
    'subtitle' => null,
    'align' => 'center',
    'level' => 'h2',
    'id' => null,
])

<div {{ $attributes->class(['max-w-3xl', 'mx-auto text-center' => $align === 'center']) }}>
    @if ($kicker)
        <p class="kicker">{{ $kicker }}</p>
    @endif
    <{{ $level }} @if ($id) id="{{ $id }}" @endif class="mt-3 text-3xl leading-tight font-bold tracking-tight text-night sm:text-4xl">
        {{ $title }}
    </{{ $level }}>
    @if ($subtitle)
        <p class="mt-3 text-base text-night/80 sm:text-lg">{{ $subtitle }}</p>
    @endif
</div>
