<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Methodology;
use App\Models\Page;
use App\Models\Prestation;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_the_mockup_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Solution Netmobile — Agence de conseil digital, web &amp; mobile</title>', false)
            ->assertSee('votre partenaire digital')
            ->assertSee('Un accompagnement digital complet')
            ->assertSeeInOrder(['Conseil digital', 'Accompagnement digital', 'Production web &amp; mobile'], false)
            ->assertSeeInOrder(['Comprendre', 'Définir', 'Produire', 'Accompagner'])
            ->assertSee('Florent Andreu')
            ->assertSee('Ils nous font confiance')
            ->assertSee('Un projet digital ?')
            ->assertSee('contact@solution-netmobile.fr')
            ->assertSee('application/ld+json', false);
    }

    public function test_navigation_links_target_every_section(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/')->assertOk();

        foreach (['accueil', 'prestations', 'methodologie', 'a-propos', 'contact'] as $section) {
            $response->assertSee('href="#'.$section.'"', false)
                ->assertSee('id="'.$section.'"', false);
        }

        $response->assertSee(route('pages.show', Page::LEGAL_NOTICE))
            ->assertSee(route('pages.show', Page::PRIVACY_POLICY));
    }

    public function test_unpublished_or_inactive_content_is_hidden(): void
    {
        Prestation::factory()->create(['title' => 'Prestation visible']);
        Prestation::factory()->unpublished()->create(['title' => 'Prestation cachée']);
        Methodology::factory()->create(['title' => 'Étape visible']);
        Methodology::factory()->inactive()->create(['title' => 'Étape cachée']);
        Client::factory()->create(['name' => 'Client visible']);
        Client::factory()->inactive()->create(['name' => 'Client caché']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Prestation visible')
            ->assertDontSee('Prestation cachée')
            ->assertSee('Étape visible')
            ->assertDontSee('Étape cachée')
            ->assertSee('Client visible')
            ->assertDontSee('Client caché');
    }

    public function test_home_page_reflects_content_changes_immediately(): void
    {
        $prestation = Prestation::factory()->create(['title' => 'Ancien titre']);
        $this->get('/')->assertSee('Ancien titre');

        $prestation->update(['title' => 'Nouveau titre']);

        $this->get('/')->assertSee('Nouveau titre')->assertDontSee('Ancien titre');
    }

    public function test_home_page_shows_an_empty_state_without_prestations(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Nos prestations seront présentées très prochainement.');
    }

    public function test_prestation_detail_page_is_displayed(): void
    {
        $prestation = Prestation::factory()->create([
            'title' => 'Audit digital',
            'slug' => 'audit-digital',
            'content' => '<p>Contenu détaillé</p><script>alert(1)</script>',
        ]);

        $this->get(route('prestations.show', $prestation))
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Audit digital')
            ->assertSee('Contenu détaillé')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('"@type":"Service"', false);
    }

    public function test_unpublished_prestation_returns_404(): void
    {
        $prestation = Prestation::factory()->unpublished()->create();

        $this->get(route('prestations.show', $prestation))->assertNotFound();
    }

    public function test_published_page_is_displayed_and_draft_is_not(): void
    {
        $published = Page::factory()->create(['title' => 'Mentions', 'slug' => 'mentions']);
        $draft = Page::factory()->draft()->create(['slug' => 'brouillon']);
        $scheduled = Page::factory()->create(['slug' => 'plus-tard', 'published_at' => now()->addWeek()]);

        $this->get(route('pages.show', $published->slug))->assertOk()->assertSee('Mentions');
        $this->get(route('pages.show', $draft->slug))->assertNotFound();
        $this->get(route('pages.show', $scheduled->slug))->assertNotFound();
    }

    public function test_sitemap_lists_public_urls_only(): void
    {
        $prestation = Prestation::factory()->create(['slug' => 'visible']);
        Prestation::factory()->unpublished()->create(['slug' => 'masquee']);
        $page = Page::factory()->create(['slug' => 'mentions-legales']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('prestations.show', $prestation), false)
            ->assertSee(route('pages.show', $page->slug), false)
            ->assertDontSee('masquee');
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_robots_txt_is_served(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *');
    }
}
