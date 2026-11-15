<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Models\Client;
use App\Services\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ClientResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAsRole(Role::Editor);
    }

    public function test_a_client_can_be_created_with_a_logo(): void
    {
        Livewire::test(CreateClient::class)
            ->fillForm([
                'name' => 'Mairie de Test',
                'website_url' => 'https://www.example.org',
                'logo' => UploadedFile::fake()->image('logo.png', 400, 120),
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $client = Client::query()->sole();
        $this->assertStringStartsWith('clients/', $client->logo);
        $this->assertStringEndsWith('.webp', $client->logo);
        Storage::disk('public')->assertExists($client->logo);

        $this->get('/')->assertSee('Mairie de Test')->assertSee('https://www.example.org', false);
    }

    public function test_logo_and_url_are_validated(): void
    {
        Livewire::test(CreateClient::class)
            ->fillForm([
                'name' => 'Client',
                'website_url' => 'javascript:alert(1)',
                'logo' => UploadedFile::fake()->create('logo.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['website_url', 'logo']);

        $this->assertDatabaseCount('clients', 0);
    }

    public function test_a_client_can_be_deactivated(): void
    {
        $client = Client::factory()->create(['name' => 'Client désactivable']);

        Livewire::test(EditClient::class, ['record' => $client->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($client->fresh()->is_active);
        $this->get('/')->assertDontSee('Client désactivable');
    }

    public function test_clients_are_listed_and_searchable(): void
    {
        $alpha = Client::factory()->create(['name' => 'Alpha Industries']);
        $beta = Client::factory()->create(['name' => 'Beta Services']);

        Livewire::test(ListClients::class)
            ->searchTable('Alpha')
            ->assertCanSeeTableRecords([$alpha])
            ->assertCanNotSeeTableRecords([$beta]);
    }

    public function test_svg_files_with_scripts_are_rejected(): void
    {
        $this->expectException(ValidationException::class);

        app(ImageOptimizer::class)->assertSafeSvg('<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
    }

    public function test_clean_svg_files_are_accepted(): void
    {
        app(ImageOptimizer::class)->assertSafeSvg('<svg xmlns="http://www.w3.org/2000/svg"><rect width="10" height="10"/></svg>');

        $this->addToAssertionCount(1);
    }
}
