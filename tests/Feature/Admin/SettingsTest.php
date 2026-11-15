<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Pages\ManageAgency;
use App\Filament\Pages\ManageSiteSettings;
use App\Services\SettingsService;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_fall_back_to_defaults(): void
    {
        $this->assertSame('contact@solution-netmobile.fr', app(SettingsService::class)->get('contact_email'));
    }

    public function test_site_settings_can_be_updated_and_are_displayed(): void
    {
        $this->seed(SiteSettingSeeder::class);
        $this->actingAsRole(Role::Admin);

        Livewire::test(ManageSiteSettings::class)
            ->assertSchemaStateSet(['contact_email' => 'contact@solution-netmobile.fr'])
            ->fillForm([
                'contact_email' => 'bonjour@netmobile.test',
                'contact_phone' => '+33 1 23 45 67 89',
                'linkedin_url' => 'https://www.linkedin.com/company/netmobile',
                'cta_title' => 'Parlons de votre projet !',
                'notification_emails' => 'equipe@netmobile.test',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('site_settings', ['key' => 'contact_email', 'value' => 'bonjour@netmobile.test']);

        $this->get('/')
            ->assertSee('bonjour@netmobile.test')
            ->assertSee('+33 1 23 45 67 89')
            ->assertSee('https://www.linkedin.com/company/netmobile', false)
            ->assertSee('Parlons de votre projet !');
    }

    public function test_invalid_settings_are_rejected(): void
    {
        $this->seed(SiteSettingSeeder::class);
        $this->actingAsRole(Role::Admin);

        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'contact_email' => 'pas-un-email',
                'notification_emails' => 'ok@example.com, faux',
                'linkedin_url' => 'http://insecure.example',
            ])
            ->call('save')
            ->assertHasFormErrors(['contact_email', 'notification_emails', 'linkedin_url']);
    }

    public function test_agency_presentation_can_be_updated(): void
    {
        $this->seed(SiteSettingSeeder::class);
        $this->actingAsRole(Role::Editor);

        Livewire::test(ManageAgency::class)
            ->fillForm([
                'agency_name' => 'Solution Netmobile',
                'agency_description' => 'Nouvelle présentation de l’agence.',
                'leader_name' => 'Florent Andreu',
                'leader_role' => 'Fondateur',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/')->assertSee('Nouvelle présentation de l’agence.')->assertSee('Fondateur');
    }

    public function test_editors_cannot_save_site_settings(): void
    {
        $this->actingAsRole(Role::Editor);

        Livewire::test(ManageSiteSettings::class)->assertForbidden();
    }
}
