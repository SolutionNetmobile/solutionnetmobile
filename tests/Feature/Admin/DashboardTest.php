<?php

namespace Tests\Feature\Admin;

use App\Enums\ContactStatus;
use App\Enums\Role;
use App\Filament\Widgets\ContactsByWeekdayChart;
use App\Filament\Widgets\ContactsTrendChart;
use App\Filament\Widgets\LatestContacts;
use App\Filament\Widgets\ProspectsByStatusChart;
use App\Filament\Widgets\StatsOverview;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Prestation;
use App\Services\DashboardStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_widgets_display_real_data(): void
    {
        $this->actingAsRole(Role::Admin);

        Contact::factory()->count(2)->status(ContactStatus::New)->create();
        Contact::factory()->status(ContactStatus::InProgress)->create(['last_name' => 'Dernier']);
        Prestation::factory()->count(3)->create();
        Prestation::factory()->unpublished()->create();
        Client::factory()->count(4)->create();

        Livewire::test(StatsOverview::class)
            ->assertSee('Demandes de contact')
            ->assertSee('Nouveaux prospects')
            ->assertSee('Prospects en cours')
            ->assertSee('Prestations actives')
            ->assertSee('Références clients')
            ->assertSee('4 au total');

        Livewire::test(ContactsTrendChart::class)->assertOk();
        Livewire::test(ContactsTrendChart::class)->set('filter', 'week')->assertOk();
        Livewire::test(ProspectsByStatusChart::class)->assertOk();
        Livewire::test(ContactsByWeekdayChart::class)->assertOk();
        Livewire::test(LatestContacts::class)->assertSee('Dernier');
    }

    public function test_statistics_are_computed_from_the_database(): void
    {
        Contact::factory()->count(2)->status(ContactStatus::Won)->create();
        Contact::factory()->status(ContactStatus::Lost)->archived()->create();

        $statistics = app(DashboardStatistics::class);

        $this->assertSame(2, $statistics->prospectsByStatus()[ContactStatus::Won->value]);
        $this->assertSame(0, $statistics->prospectsByStatus()[ContactStatus::Lost->value]);
        $this->assertSame(3, array_sum($statistics->lastSevenDays()));
        $this->assertCount(12, $statistics->requestsOverPeriod('year')['labels']);
        $this->assertSame(3, array_sum($statistics->requestsByWeekday()['values']));
    }

    public function test_editors_do_not_see_prospect_widgets(): void
    {
        $this->actingAsRole(Role::Editor);

        $this->assertFalse(ContactsTrendChart::canView());
        $this->assertFalse(LatestContacts::canView());

        Livewire::test(StatsOverview::class)
            ->assertDontSee('Demandes de contact')
            ->assertSee('Prestations actives');
    }
}
