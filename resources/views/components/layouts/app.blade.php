@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'schema' => [],
    'home' => false,
])

@php
    $siteName = site()->get('site_name');
    $pageTitle = $title ? $title.' | '.$siteName : site()->get('seo_title');
    $metaDescription = \Illuminate\Support\Str::limit(strip_tags($description ?? site()->get('seo_description')), 160);
    $ogImage = $image ?? site()->url('og_image', asset('images/og-default.png'));
    $canonical = url()->current();

    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'ProfessionalService',
        'name' => $siteName,
        'description' => site()->get('seo_description'),
        'url' => route('home'),
        'logo' => site()->url('logo', asset('images/logo-solution-netmobile.svg')),
        'email' => site()->get('contact_email'),
        'telephone' => site()->get('contact_phone'),
        'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'FR', 'addressLocality' => site()->get('contact_location')],
        'areaServed' => 'FR',
        'founder' => ['@type' => 'Person', 'name' => site()->get('leader_name'), 'jobTitle' => site()->get('leader_role')],
        'sameAs' => array_values(array_filter([site()->get('linkedin_url')])),
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    @unless (app()->isProduction())
        <meta name="robots" content="noindex, nofollow">
    @endunless

    <meta property="og:type" content="{{ $type }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ site()->url('favicon', asset('favicon.svg')) }}">
    <meta name="theme-color" content="#0755C8">

    <script type="application/ld+json">{!! json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @foreach ($schema as $item)
        <script type="application/ld+json">{!! json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a href="#contenu" class="sr-only z-[60] rounded-md bg-brand-600 px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">
        Aller au contenu
    </a>

    <x-site.header :home="$home" />

    <main id="contenu" class="flex-1">
        {{ $slot }}
    </main>

    <x-site.footer />
</body>
</html>
