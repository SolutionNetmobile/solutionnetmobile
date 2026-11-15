@php
    $links = [
        '' => 'Accueil',
        '#prestations' => 'Prestations',
        '#methodologie' => 'Méthodologie',
        '#a-propos' => 'À propos',
        '#contact' => 'Contact',
    ];
    $email = site()->get('contact_email');
    $phone = site()->get('contact_phone');
    $linkedin = site()->get('linkedin_url');
@endphp

<footer class="border-t border-brand-100 bg-brand-50/60 text-night">
    <div class="container-site py-10 lg:py-12">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[auto_minmax(0,1.6fr)_minmax(0,0.9fr)_minmax(0,1.3fr)_auto] lg:gap-0">
            <div class="flex items-start lg:pr-10">
                <a href="{{ route('home') }}" aria-label="{{ site()->get('site_name') }} — accueil" class="rounded-md">
                    <x-site.logo variant="footer" class="h-12 w-auto" />
                </a>
            </div>

            <div class="text-sm lg:border-r lg:border-brand-100 lg:pr-10">
                <p class="text-base font-semibold">{{ site()->get('site_name') }}</p>
                <p class="mt-1 text-muted">{{ site()->get('site_tagline') }}</p>
                <p class="mt-1 text-muted">{{ site()->get('site_activities') }}</p>
            </div>

            <nav aria-label="Navigation du pied de page" class="lg:border-r lg:border-brand-100 lg:px-10">
                <ul class="space-y-1.5 text-sm">
                    @foreach ($links as $anchor => $label)
                        <li>
                            <a href="{{ route('home').$anchor }}" class="text-night/80 transition-colors hover:text-brand-600">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <ul class="space-y-2.5 text-sm lg:border-r lg:border-brand-100 lg:px-10">
                @if ($email)
                    <li>
                        <a href="mailto:{{ $email }}" class="inline-flex items-center gap-3 break-all text-night/80 transition-colors hover:text-brand-600">
                            <x-site.icon name="mail" class="size-4.5 shrink-0 text-brand-600" />
                            {{ $email }}
                        </a>
                    </li>
                @endif
                @if ($phone)
                    <li>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="inline-flex items-center gap-3 text-night/80 transition-colors hover:text-brand-600">
                            <x-site.icon name="phone" class="size-4.5 shrink-0 text-brand-600" />
                            {{ $phone }}
                        </a>
                    </li>
                @endif
                @if (site()->get('contact_location'))
                    <li class="inline-flex items-center gap-3 text-night/80">
                        <x-site.icon name="map-pin" class="size-4.5 shrink-0 text-brand-600" />
                        {{ site()->get('contact_location') }}
                    </li>
                @endif
            </ul>

            @if ($linkedin)
                <div class="flex items-start lg:pl-10">
                    <a
                        href="{{ $linkedin }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex size-10 items-center justify-center rounded-md bg-brand-600 text-white transition hover:-translate-y-0.5 hover:bg-brand-700"
                        aria-label="{{ site()->get('site_name') }} sur LinkedIn (nouvel onglet)"
                    >
                        <x-site.icon name="linkedin" class="size-5" stroke-width="2" />
                    </a>
                </div>
            @endif
        </div>

        <div class="mt-10 flex flex-col gap-4 border-t border-brand-100 pt-6 text-xs text-muted sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} {{ site()->get('copyright') }}</p>
            <ul class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <li>
                    <a href="{{ route('pages.show', \App\Models\Page::LEGAL_NOTICE) }}" class="transition-colors hover:text-brand-600">Mentions légales</a>
                </li>
                <li aria-hidden="true" class="text-brand-200">|</li>
                <li>
                    <a href="{{ route('pages.show', \App\Models\Page::PRIVACY_POLICY) }}" class="transition-colors hover:text-brand-600">Politique de confidentialité</a>
                </li>
            </ul>
        </div>
    </div>
</footer>
