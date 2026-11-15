<x-layouts.app
    :title="$page->meta_title ?: $page->title"
    :description="$page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $page->content), 160)"
    :image="$page->image_url"
    type="article"
>
    <section class="bg-gradient-to-b from-brand-50 to-white">
        <div class="container-site py-12 sm:py-16">
            <nav aria-label="Fil d’Ariane" class="text-sm text-muted">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-600">Accueil</a></li>
                    <li aria-hidden="true"><x-site.icon name="chevron-right" class="size-3.5" /></li>
                    <li aria-current="page" class="font-medium text-night">{{ $page->title }}</li>
                </ol>
            </nav>
            <h1 class="mt-6 text-4xl font-bold tracking-tight text-night sm:text-5xl">{{ $page->title }}</h1>
            <p class="mt-3 text-sm text-muted">Dernière mise à jour : {{ $page->updated_at->translatedFormat('d F Y') }}</p>
        </div>
    </section>

    <article class="container-site pb-16 sm:pb-20">
        @if ($page->image_url)
            <img src="{{ $page->image_url }}" alt="" loading="lazy" decoding="async" class="mb-10 aspect-[21/9] w-full max-w-4xl rounded-[var(--radius-card)] object-cover">
        @endif
        @if ($page->slug === \App\Models\Page::LEGAL_NOTICE)
            @php
                $legal = array_filter([
                    'Raison sociale' => site()->get('company_legal_name'),
                    'Forme juridique' => site()->get('company_legal_form'),
                    'SIRET' => site()->get('company_siret'),
                    'Siège' => site()->get('company_address'),
                    'Directeur de la publication' => site()->get('company_publication_director'),
                    'E-mail' => site()->get('contact_email'),
                    'Téléphone' => site()->get('contact_phone'),
                ]);
            @endphp
            <dl class="mb-10 grid max-w-3xl gap-x-8 gap-y-3 rounded-[var(--radius-card)] border border-brand-100 bg-brand-50/60 p-6 text-sm sm:grid-cols-[auto_1fr]">
                @foreach ($legal as $label => $value)
                    <dt class="font-semibold text-night">{{ $label }}</dt>
                    <dd class="text-night/80">{{ $value }}</dd>
                @endforeach
            </dl>
        @endif

        <div class="prose prose-slate max-w-3xl prose-headings:text-night prose-a:text-brand-600">
            {!! \App\Support\Html::clean($page->content) !!}
        </div>
    </article>
</x-layouts.app>
