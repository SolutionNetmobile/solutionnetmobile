<x-layouts.app
    :title="$prestation->meta_title ?: $prestation->title"
    :description="$prestation->meta_description ?: $prestation->excerpt"
    :image="$prestation->image_url"
    :schema="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $prestation->title,
            'description' => $prestation->excerpt,
            'url' => route('prestations.show', $prestation),
            'provider' => ['@type' => 'ProfessionalService', 'name' => site()->get('site_name'), 'url' => route('home')],
            'areaServed' => 'FR',
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Prestations', 'item' => route('home').'#prestations'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $prestation->title],
            ],
        ],
    ]"
>
    <section class="relative isolate overflow-hidden bg-gradient-to-b from-brand-50 to-white">
        <div class="container-site py-12 sm:py-16">
            <nav aria-label="Fil d’Ariane" class="text-sm text-muted">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-600">Accueil</a></li>
                    <li aria-hidden="true"><x-site.icon name="chevron-right" class="size-3.5" /></li>
                    <li><a href="{{ route('home') }}#prestations" class="hover:text-brand-600">Prestations</a></li>
                    <li aria-hidden="true"><x-site.icon name="chevron-right" class="size-3.5" /></li>
                    <li aria-current="page" class="font-medium text-night">{{ $prestation->title }}</li>
                </ol>
            </nav>

            <div class="mt-8 flex flex-col gap-6 sm:flex-row sm:items-center">
                <span class="inline-flex size-20 shrink-0 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-[var(--shadow-card)] ring-1 ring-brand-100">
                    <x-site.icon :name="$prestation->icon ?? 'sparkles'" class="size-11" stroke-width="1.5" />
                </span>
                <div>
                    <p class="kicker">Prestation</p>
                    <h1 class="mt-2 text-4xl font-bold tracking-tight text-night sm:text-5xl">{{ $prestation->title }}</h1>
                </div>
            </div>
            <p class="mt-6 max-w-3xl text-lg leading-relaxed text-night/85 sm:text-xl">{{ $prestation->excerpt }}</p>
        </div>
    </section>

    <section class="py-12 sm:py-16">
        <div class="container-site grid gap-12 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div>
                @if ($prestation->image_url)
                    <img src="{{ $prestation->image_url }}" alt="{{ $prestation->title }}" loading="lazy" decoding="async" class="mb-10 aspect-[16/9] w-full rounded-[var(--radius-card)] object-cover">
                @endif

                @if (filled($prestation->content))
                    <div class="prose prose-lg prose-slate max-w-none prose-headings:font-bold prose-headings:text-night prose-a:text-brand-600 prose-li:marker:text-brand-600">
                        {!! \App\Support\Html::clean($prestation->content) !!}
                    </div>
                @endif

                <div class="mt-10">
                    <x-button :href="route('home').'#contact'" icon="arrow-right" size="lg">Discuter de votre projet</x-button>
                </div>
            </div>

            @if ($otherPrestations->isNotEmpty())
                <aside aria-labelledby="autres-prestations">
                    <h2 id="autres-prestations" class="text-lg font-bold text-night">Nos autres prestations</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($otherPrestations as $other)
                            <li>
                                <a href="{{ route('prestations.show', $other) }}" class="group flex items-center gap-4 rounded-xl border border-brand-100 bg-brand-50/60 p-4 transition hover:border-brand-200 hover:bg-white hover:shadow-[var(--shadow-card)]">
                                    <x-site.icon :name="$other->icon ?? 'sparkles'" class="size-8 shrink-0 text-brand-600" />
                                    <span class="font-semibold text-night group-hover:text-brand-600">{{ $other->title }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif
        </div>
    </section>

    <div class="container-site pb-16">
        <x-cta />
    </div>
</x-layouts.app>
