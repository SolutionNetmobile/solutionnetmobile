@props(['prestation'])

<article {{ $attributes->class('group relative flex h-full flex-col rounded-[var(--radius-card)] border border-brand-100 bg-gradient-to-br from-brand-50 to-white p-7 shadow-[var(--shadow-card)] transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-[var(--shadow-card-hover)]') }}>
    @if ($prestation->image_url)
        <img src="{{ $prestation->image_url }}" alt="" loading="lazy" decoding="async" class="mb-5 aspect-[16/9] w-full rounded-lg object-cover">
    @endif

    <span class="inline-flex text-brand-600">
        <x-site.icon :name="$prestation->icon ?? 'sparkles'" class="size-12 transition-transform duration-300 group-hover:scale-110" stroke-width="1.5" />
    </span>

    <h3 class="mt-4 text-xl font-bold text-night sm:text-[1.4rem]">
        <a href="{{ route('prestations.show', $prestation) }}" class="after:absolute after:inset-0 after:rounded-[var(--radius-card)] focus-visible:outline-none">
            {{ $prestation->title }}
        </a>
    </h3>

    <p class="mt-2 text-[0.97rem] leading-relaxed text-night/80">{{ $prestation->excerpt }}</p>

    <span class="mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold text-brand-600 opacity-80 transition group-hover:opacity-100 group-focus-within:opacity-100">
        En savoir plus
        <x-site.icon name="arrow-right" class="size-4 transition-transform group-hover:translate-x-1" />
    </span>
</article>
