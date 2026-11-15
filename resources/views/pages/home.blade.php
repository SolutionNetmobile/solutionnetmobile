<x-layouts.app :home="true" :schema="[[
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => site()->get('site_name'),
    'url' => route('home'),
    'inLanguage' => 'fr-FR',
]]">

    {{-- ============ HERO ============ --}}
    <section id="accueil" class="relative isolate overflow-hidden bg-gradient-to-b from-brand-50 via-brand-50/60 to-white">
        <svg class="pointer-events-none absolute -top-24 right-0 -z-10 h-[38rem] w-[52rem] max-w-none opacity-90" viewBox="0 0 800 600" aria-hidden="true">
            <path d="M260 0h540v600C690 560 560 470 470 360 380 250 330 120 260 0z" fill="#DCE8FC" />
            <path d="M520 0h280v380C700 300 600 180 520 0z" fill="#C7DBFA" opacity="0.8" />
        </svg>

        <div class="container-site grid items-center gap-12 py-14 sm:py-16 lg:grid-cols-[1.05fr_1fr] lg:gap-8 lg:py-20">
            <div class="animate-fade-up">
                <p class="kicker">{{ site()->get('hero_kicker') }}</p>
                <h1 class="mt-5 text-4xl leading-[1.08] font-bold tracking-tight text-night sm:text-5xl lg:text-[3.4rem]">
                    {{ site()->get('hero_title') }}
                    <span class="block text-brand-600">{{ site()->get('hero_title_highlight') }}</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-night/85 sm:text-xl">
                    {{ site()->get('hero_description') }}
                </p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:gap-4">
                    <x-button href="#contact" icon="arrow-right" size="lg">{{ site()->get('hero_primary_button') }}</x-button>
                    <x-button href="#prestations" variant="outline" size="lg">{{ site()->get('hero_secondary_button') }}</x-button>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-xl lg:max-w-none">
                @if ($heroImage = site()->url('hero_image'))
                    <img src="{{ $heroImage }}" alt="Ordinateur portable et smartphone affichant des interfaces web et mobiles" class="h-auto w-full" fetchpriority="high" decoding="async">
                @else
                    <x-hero-illustration class="h-auto w-full" />
                @endif
            </div>
        </div>
    </section>

    {{-- ============ PRESTATIONS ============ --}}
    <section id="prestations" aria-labelledby="prestations-title" class="pt-10 pb-16 sm:pb-20">
        <div class="container-site">
            <x-section-heading
                id="prestations-title"
                :kicker="site()->get('services_kicker')"
                :title="site()->get('services_title')"
                :subtitle="site()->get('services_subtitle')"
            />

            @if ($prestations->isNotEmpty())
                <ul class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($prestations as $prestation)
                        <li class="animate-fade-up" style="animation-delay: {{ 150 + $loop->index * 90 }}ms">
                            <x-prestation-card :prestation="$prestation" />
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-12 rounded-[var(--radius-card)] border border-dashed border-brand-200 bg-brand-50 p-8 text-center text-muted">
                    Nos prestations seront présentées très prochainement.
                </p>
            @endif
        </div>
    </section>

    {{-- ============ MÉTHODOLOGIE ============ --}}
    <section id="methodologie" aria-labelledby="methodologie-title" class="bg-gradient-to-r from-brand-100/80 to-brand-50 py-14 sm:py-16">
        <div class="container-site grid gap-10 lg:grid-cols-[minmax(0,19rem)_1fr] lg:items-center lg:gap-12">
            <x-section-heading
                id="methodologie-title"
                align="left"
                :kicker="site()->get('methodology_kicker')"
                :title="site()->get('methodology_title')"
            />

            @if ($methodologies->isNotEmpty())
                <ol class="relative grid gap-8 sm:grid-cols-2 lg:flex lg:items-start lg:justify-between lg:gap-2">
                    @foreach ($methodologies as $step)
                        <li class="relative flex gap-5 lg:flex-1 lg:flex-col lg:items-center lg:gap-0 lg:text-center">
                            <span class="relative z-10 inline-flex size-16 shrink-0 items-center justify-center rounded-full bg-white text-brand-600 shadow-[var(--shadow-card)] ring-1 ring-brand-100">
                                <x-site.icon :name="$step->icon ?? 'sparkles'" class="size-8" stroke-width="1.6" />
                            </span>
                            <div class="lg:mt-4">
                                <h3 class="text-lg font-bold text-night">
                                    <span class="text-brand-600 lg:text-night">{{ $loop->iteration }}.</span> {{ $step->title }}
                                </h3>
                                <p class="mt-1 text-[0.95rem] leading-snug text-night/80 lg:mx-auto lg:max-w-[11rem]">{{ $step->description }}</p>
                            </div>

                            @unless ($loop->last)
                                <x-site.icon name="arrow-right" class="absolute top-6 -right-4 hidden size-5 text-brand-600 lg:block" />
                            @endunless
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </section>

    {{-- ============ À PROPOS ============ --}}
    <section id="a-propos" aria-labelledby="a-propos-title" class="py-16 sm:py-20">
        <div class="container-site grid gap-10 lg:grid-cols-[1.15fr_1fr] lg:gap-0">
            <div class="lg:border-r lg:border-brand-100 lg:pr-14">
                <p class="kicker">{{ site()->get('about_kicker') }}</p>
                <h2 id="a-propos-title" class="mt-4 text-3xl font-bold tracking-tight text-night sm:text-4xl">{{ site()->get('agency_name') }}</h2>
                <p class="mt-4 text-lg leading-relaxed text-night/85">{{ site()->get('agency_description') }}</p>

                @if (site()->get('agency_presentation'))
                    <div class="prose prose-slate mt-5 max-w-none text-night/80">{!! \App\Support\Html::clean(site()->get('agency_presentation')) !!}</div>
                @endif
            </div>

            <div class="flex gap-5 lg:pt-8 lg:pl-14">
                @if ($photo = site()->url('leader_photo'))
                    <img src="{{ $photo }}" alt="Photo de {{ site()->get('leader_name') }}" loading="lazy" class="size-20 shrink-0 rounded-full object-cover ring-4 ring-brand-50">
                @else
                    <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <x-site.icon name="calendar-days" class="size-7" />
                    </span>
                @endif

                <div>
                    <p class="text-base text-night/80">{{ site()->get('leader_label') }}</p>
                    <p class="text-2xl font-bold text-night">{{ site()->get('leader_name') }}</p>
                    @if (site()->get('leader_role'))
                        <p class="text-sm font-medium tracking-wide text-brand-600 uppercase">{{ site()->get('leader_role') }}</p>
                    @endif
                    <p class="mt-3 leading-relaxed text-night/80">{{ site()->get('leader_description') }}</p>

                    @php
                        $leaderLinks = array_filter([
                            'mail' => site()->get('leader_email') ? ['mailto:'.site()->get('leader_email'), site()->get('leader_email')] : null,
                            'phone' => site()->get('leader_phone') ? ['tel:'.preg_replace('/[^0-9+]/', '', site()->get('leader_phone')), site()->get('leader_phone')] : null,
                            'linkedin' => site()->get('leader_linkedin') ? [site()->get('leader_linkedin'), 'Profil LinkedIn'] : null,
                        ]);
                    @endphp
                    @if ($leaderLinks)
                        <ul class="mt-4 space-y-1.5 text-sm">
                            @foreach ($leaderLinks as $icon => [$url, $label])
                                <li>
                                    <a href="{{ $url }}" @if ($icon === 'linkedin') target="_blank" rel="noopener noreferrer" @endif class="inline-flex items-center gap-2 text-night/80 hover:text-brand-600">
                                        <x-site.icon :name="$icon" class="size-4 text-brand-600" /> {{ $label }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if (site()->get('agency_extra_info'))
                        <p class="mt-4 text-sm leading-relaxed text-muted">{{ site()->get('agency_extra_info') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ============ RÉFÉRENCES CLIENTS ============ --}}
    @if ($clients->isNotEmpty())
        <section aria-labelledby="clients-title" class="bg-brand-50/80 py-8">
            <div class="container-site flex flex-col gap-6 lg:flex-row lg:items-center lg:gap-10">
                <div class="flex shrink-0 items-center gap-6">
                    <h2 id="clients-title" class="text-lg font-bold text-night">{{ site()->get('clients_title') }}</h2>
                    <span class="hidden h-px w-10 bg-brand-600 lg:block" aria-hidden="true"></span>
                </div>
                <ul class="grid flex-1 grid-cols-2 items-center gap-x-8 gap-y-4 sm:grid-cols-3 lg:flex lg:justify-between">
                    @foreach ($clients as $client)
                        <x-client-logo :client="$client" />
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ============ CTA ============ --}}
    <div class="container-site py-6 sm:py-10">
        <x-cta href="#contact" />
    </div>

    {{-- ============ CONTACT ============ --}}
    <section id="contact" aria-labelledby="contact-title" class="pt-6 pb-16 sm:pb-20">
        <div class="container-site grid gap-10 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:gap-14">
            <div>
                <p class="kicker">Contact</p>
                <h2 id="contact-title" class="mt-4 text-3xl font-bold tracking-tight text-night sm:text-4xl">{{ site()->get('contact_title') }}</h2>
                <p class="mt-4 text-lg leading-relaxed text-night/80">{{ site()->get('contact_subtitle') }}</p>

                <ul class="mt-8 space-y-4">
                    @if (site()->get('contact_email'))
                        <li>
                            <a href="mailto:{{ site()->get('contact_email') }}" class="group flex items-center gap-4">
                                <span class="inline-flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white"><x-site.icon name="mail" class="size-5" /></span>
                                <span><span class="block text-xs tracking-wide text-muted uppercase">E-mail</span><span class="font-medium break-all text-night group-hover:text-brand-600">{{ site()->get('contact_email') }}</span></span>
                            </a>
                        </li>
                    @endif
                    @if (site()->get('contact_phone'))
                        <li>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', site()->get('contact_phone')) }}" class="group flex items-center gap-4">
                                <span class="inline-flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white"><x-site.icon name="phone" class="size-5" /></span>
                                <span><span class="block text-xs tracking-wide text-muted uppercase">Téléphone</span><span class="font-medium text-night group-hover:text-brand-600">{{ site()->get('contact_phone') }}</span></span>
                            </a>
                        </li>
                    @endif
                    @if (site()->get('contact_location'))
                        <li class="flex items-center gap-4">
                            <span class="inline-flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-site.icon name="map-pin" class="size-5" /></span>
                            <span><span class="block text-xs tracking-wide text-muted uppercase">Localisation</span><span class="font-medium text-night">{{ site()->get('contact_location') }}</span></span>
                        </li>
                    @endif
                </ul>
            </div>

            <x-contact-form :project-types="$projectTypes" :budgets="$budgets" />
        </div>
    </section>
</x-layouts.app>
