<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\Methodologies\Pages\CreateMethodology;
use App\Filament\Resources\Prestations\Pages\CreatePrestation;
use App\Filament\Resources\Prestations\Pages\EditPrestation;
use App\Filament\Resources\Prestations\Pages\ListPrestations;
use App\Models\Methodology;
use App\Models\Prestation;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrestationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsRole(Role::Editor);
    }

    public function test_prestations_are_listed(): void
    {
        $prestations = Prestation::factory()->count(3)->create();

        Livewire::test(ListPrestations::class)
            ->assertOk()
            ->assertCanSeeTableRecords($prestations);
    }

    public function test_a_prestation_can_be_created(): void
    {
        Livewire::test(CreatePrestation::class)
            ->fillForm([
                'title' => 'Audit SEO',
                'slug' => 'audit-seo',
                'excerpt' => 'Analyse complète de votre référencement naturel.',
                'icon' => 'search',
                'sort_order' => 4,
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('prestations', ['slug' => 'audit-seo', 'icon' => 'search', 'is_published' => true]);
        $this->get('/')->assertSee('Audit SEO');
    }

    public function test_validation_rules_are_enforced(): void
    {
        Prestation::factory()->create(['slug' => 'deja-pris']);

        Livewire::test(CreatePrestation::class)
            ->fillForm([
                'title' => '',
                'slug' => 'deja-pris',
                'excerpt' => '',
                'icon' => 'icone-inexistante',
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'slug' => 'unique', 'excerpt' => 'required', 'icon']);
    }

    public function test_a_prestation_can_be_updated_and_unpublished(): void
    {
        $prestation = Prestation::factory()->create(['title' => 'Avant']);

        Livewire::test(EditPrestation::class, ['record' => $prestation->getRouteKey()])
            ->fillForm(['title' => 'Après', 'is_published' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $prestation->refresh();
        $this->assertSame('Après', $prestation->title);
        $this->assertFalse($prestation->is_published);
        $this->get('/')->assertDontSee('Après');
    }

    public function test_a_prestation_can_be_soft_deleted(): void
    {
        $prestation = Prestation::factory()->create();

        Livewire::test(EditPrestation::class, ['record' => $prestation->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertSoftDeleted($prestation);
    }

    public function test_prestations_can_be_reordered(): void
    {
        [$first, $second] = Prestation::factory()->count(2)->sequence(['sort_order' => 1], ['sort_order' => 2])->create();

        Livewire::test(ListPrestations::class)
            ->call('reorderTable', [(string) $second->getKey(), (string) $first->getKey()]);

        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);
    }

    public function test_a_methodology_step_can_be_created(): void
    {
        Livewire::test(CreateMethodology::class)
            ->fillForm([
                'title' => 'Mesurer',
                'description' => 'Les résultats obtenus',
                'icon' => 'chart-line',
                'sort_order' => 5,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(1, Methodology::query()->where('title', 'Mesurer')->count());
    }
}
