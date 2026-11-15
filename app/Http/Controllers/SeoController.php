<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Prestation;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'lastmod' => max(
                Prestation::query()->published()->max('updated_at'),
                Page::query()->published()->max('updated_at'),
            ), 'priority' => '1.0'],
        ]);

        Prestation::query()->published()->ordered()->get(['slug', 'updated_at'])
            ->each(fn (Prestation $prestation) => $urls->push([
                'loc' => route('prestations.show', $prestation),
                'lastmod' => $prestation->updated_at,
                'priority' => '0.8',
            ]));

        Page::query()->published()->get(['slug', 'updated_at'])
            ->each(fn (Page $page) => $urls->push([
                'loc' => route('pages.show', $page),
                'lastmod' => $page->updated_at,
                'priority' => '0.3',
            ]));

        return response()
            ->view('seo.sitemap', ['urls' => $urls], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /admin', 'Allow: /', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
