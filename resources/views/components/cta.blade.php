@props(['href' => route('home').'#contact'])

<section aria-labelledby="cta-title" {{ $attributes->class('px-0 sm:px-4 lg:px-0') }}>
    <div class="relative isolate overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-brand-500 px-6 py-12 text-center text-white sm:rounded-3xl sm:py-14 lg:rounded-[2rem]">
        {{-- Abstract shapes --}}
        <svg class="pointer-events-none absolute inset-0 -z-10 h-full w-full" viewBox="0 0 1200 300" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 0h360C250 60 130 90 0 100z" fill="#FFFFFF" opacity="0.06" />
            <path d="M0 300V180c120 10 230 60 300 120z" fill="#FFFFFF" opacity="0.05" />
            <path d="M1200 0v300H860C960 230 1060 120 1200 0z" fill="#FFFFFF" opacity="0.10" />
            <path d="M1200 90v210h-170c70-60 120-130 170-210z" fill="#FFFFFF" opacity="0.10" />
        </svg>

        <h2 id="cta-title" class="text-3xl font-bold tracking-tight sm:text-4xl">{{ site()->get('cta_title') }}</h2>
        <p class="mx-auto mt-3 max-w-2xl text-base text-white/90 sm:text-lg">{{ site()->get('cta_description') }}</p>
        <x-button :href="$href" variant="white" icon-left="mail" class="mt-7 min-w-[12rem]">
            {{ site()->get('cta_button') }}
        </x-button>
    </div>
</section>
