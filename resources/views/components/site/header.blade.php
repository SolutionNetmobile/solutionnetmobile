@props(['home' => false])

@php
    $items = [
        'accueil' => 'Accueil',
        'prestations' => 'Prestations',
        'methodologie' => 'Méthodologie',
        'a-propos' => 'À propos',
        'contact' => 'Contact',
    ];

    $href = fn (string $id): string => $home
        ? ($id === 'accueil' ? '#accueil' : '#'.$id)
        : route('home').($id === 'accueil' ? '' : '#'.$id);

    $staticActive = request()->routeIs('prestations.show') ? 'prestations' : null;
@endphp

<header
    x-data="siteHeader(@js($home ? array_keys($items) : []))"
    x-init="active = active ?? @js($staticActive)"
    @keydown.escape.window="close()"
    class="sticky top-0 z-50 bg-white/95 backdrop-blur transition-shadow duration-300"
    :class="scrolled ? 'shadow-[0_6px_24px_-12px_rgba(7,19,61,0.25)]' : ''"
>
    <div class="container-site flex h-[4.5rem] items-center justify-between gap-6 lg:h-[5rem]">
        <a href="{{ $href('accueil') }}" class="shrink-0 rounded-md" aria-label="{{ site()->get('site_name') }} — accueil">
            <x-site.logo class="h-11 w-auto lg:h-[3.4rem]" />
        </a>

        <nav aria-label="Navigation principale" class="hidden lg:block">
            <ul class="flex items-center gap-1 xl:gap-3">
                @foreach ($items as $id => $label)
                    <li>
                        <a
                            href="{{ $href($id) }}"
                            class="relative inline-flex px-3 py-2 text-[0.95rem] font-medium text-night/80 transition-colors hover:text-brand-600"
                            :class="isActive('{{ $id }}') && 'text-brand-600!'"
                            :aria-current="isActive('{{ $id }}') ? 'page' : null"
                        >
                            {{ $label }}
                            <span
                                class="absolute inset-x-3 -bottom-[0.6rem] h-0.5 origin-left scale-x-0 rounded-full bg-brand-600 transition-transform duration-300"
                                :class="isActive('{{ $id }}') && 'scale-x-100'"
                                aria-hidden="true"
                            ></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center gap-3">
            <div class="hidden sm:block">
                <x-button :href="$href('contact')" icon="arrow-right" size="sm" class="whitespace-nowrap lg:px-5 lg:py-2.5">
                    Nous contacter
                </x-button>
            </div>

            <button
                type="button"
                class="inline-flex size-11 items-center justify-center rounded-lg text-night transition hover:bg-brand-50 lg:hidden"
                @click="open = !open"
                :aria-expanded="open.toString()"
                aria-controls="menu-mobile"
            >
                <span class="sr-only" x-text="open ? 'Fermer le menu' : 'Ouvrir le menu'">Ouvrir le menu</span>
                <x-site.icon name="menu" class="size-6" x-show="!open" />
                <x-site.icon name="x" class="size-6" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    <div
        id="menu-mobile"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="border-t border-slate-100 bg-white shadow-lg lg:hidden"
    >
        <nav aria-label="Navigation mobile" class="container-site py-4">
            <ul class="flex flex-col gap-1">
                @foreach ($items as $id => $label)
                    <li>
                        <a
                            href="{{ $href($id) }}"
                            @click="close()"
                            class="flex items-center justify-between rounded-lg px-4 py-3 text-base font-medium text-night transition hover:bg-brand-50"
                            :class="isActive('{{ $id }}') && 'bg-brand-50 text-brand-600'"
                        >
                            {{ $label }}
                            <x-site.icon name="chevron-right" class="size-4 text-brand-400" />
                        </a>
                    </li>
                @endforeach
            </ul>
            <x-button :href="$href('contact')" icon="arrow-right" class="mt-4 w-full sm:hidden" x-on:click="close()">
                Nous contacter
            </x-button>
        </nav>
    </div>
</header>
