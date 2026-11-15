<?php

namespace Tests\Feature\Admin;

use App\Enums\ContactNoteType;
use App\Enums\ContactStatus;
use App\Enums\Role;
use App\Filament\Resources\Contacts\Pages\ListContacts;
use App\Filament\Resources\Contacts\Pages\ViewContact;
use App\Filament\Resources\Contacts\RelationManagers\NotesRelationManager;
use App\Models\Contact;
use App\Services\ContactExporter;
use App\Services\ContactService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_prospects_can_be_searched_and_filtered_by_status(): void
    {
        $this->actingAsRole(Role::Admin);

        $dupont = Contact::factory()->status(ContactStatus::New)->create(['last_name' => 'Dupont']);
        $martin = Contact::factory()->status(ContactStatus::Won)->create(['last_name' => 'Martin']);

        Livewire::test(ListContacts::class)
            ->assertCanSeeTableRecords([$dupont, $martin])
            ->searchTable('Dupont')
            ->assertCanSeeTableRecords([$dupont])
            ->assertCanNotSeeTableRecords([$martin]);

        Livewire::test(ListContacts::class)
            ->filterTable('status', [ContactStatus::Won->value])
            ->assertCanSeeTableRecords([$martin])
            ->assertCanNotSeeTableRecords([$dupont]);
    }

    public function test_changing_the_status_is_logged_in_the_history(): void
    {
        $user = $this->actingAsRole(Role::Admin);
        $contact = Contact::factory()->create();

        Livewire::test(ViewContact::class, ['record' => $contact->getRouteKey()])
            ->callAction('changeStatus', data: ['status' => ContactStatus::QuoteSent->value, 'comment' => 'Devis de 8 000 € envoyé.'])
            ->assertHasNoActionErrors();

        $contact->refresh();
        $this->assertSame(ContactStatus::QuoteSent, $contact->status);
        $this->assertDatabaseHas('contact_notes', [
            'contact_id' => $contact->id,
            'type' => ContactNoteType::StatusChange->value,
            'body' => 'Statut modifié : Nouveau → Devis envoyé',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('contact_notes', ['contact_id' => $contact->id, 'body' => 'Devis de 8 000 € envoyé.']);
    }

    public function test_internal_notes_can_be_added(): void
    {
        $user = $this->actingAsRole(Role::Admin);
        $contact = Contact::factory()->create();

        Livewire::test(NotesRelationManager::class, ['ownerRecord' => $contact, 'pageClass' => ViewContact::class])
            ->callAction(TestAction::make('create')->table(), data: ['type' => ContactNoteType::Call->value, 'body' => 'Rappel prévu lundi.'])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('contact_notes', [
            'contact_id' => $contact->id,
            'user_id' => $user->id,
            'type' => ContactNoteType::Call->value,
            'body' => 'Rappel prévu lundi.',
        ]);
    }

    public function test_internal_notes_are_never_exposed_publicly(): void
    {
        $contact = Contact::factory()->create();
        $contact->notes()->create(['type' => ContactNoteType::Note, 'body' => 'Note strictement interne']);

        $this->get('/')->assertDontSee('Note strictement interne');
    }

    public function test_a_prospect_can_be_archived_and_moves_to_the_archive_tab(): void
    {
        $this->actingAsRole(Role::Admin);
        $contact = Contact::factory()->create();

        Livewire::test(ViewContact::class, ['record' => $contact->getRouteKey()])
            ->callAction('archive');

        $this->assertNotNull($contact->fresh()->archived_at);

        Livewire::test(ListContacts::class)->assertCanNotSeeTableRecords([$contact]);
        Livewire::test(ListContacts::class, ['activeTab' => 'archived'])->assertCanSeeTableRecords([$contact]);
    }

    public function test_a_prospect_can_be_erased_for_gdpr(): void
    {
        $this->actingAsRole(Role::Admin);
        $contact = Contact::factory()->create();
        $contact->notes()->create(['type' => ContactNoteType::Note, 'body' => 'Note']);

        Livewire::test(ViewContact::class, ['record' => $contact->getRouteKey()])
            ->callAction('erase');

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
        $this->assertDatabaseMissing('contact_notes', ['contact_id' => $contact->id]);
    }

    public function test_prospects_can_be_exported_as_csv(): void
    {
        $this->actingAsRole(Role::Admin);
        Contact::factory()->create(['last_name' => 'Export', 'company' => '=HYPERLINK("x")']);

        Livewire::test(ListContacts::class)
            ->callAction('export')
            ->assertFileDownloaded();

        $row = app(ContactExporter::class)->row(Contact::query()->sole());
        $this->assertSame('Export', $row[2]);
        $this->assertSame('\'=HYPERLINK("x")', $row[4]);
    }

    public function test_expired_requests_are_purged(): void
    {
        $old = Contact::factory()->create();
        $old->forceFill(['updated_at' => now()->subMonths(40)])->saveQuietly();
        $recent = Contact::factory()->create();

        $deleted = app(ContactService::class)->purgeExpired();

        $this->assertSame(1, $deleted);
        $this->assertDatabaseMissing('contacts', ['id' => $old->id]);
        $this->assertDatabaseHas('contacts', ['id' => $recent->id]);
    }

    public function test_editors_cannot_manage_prospects(): void
    {
        $this->actingAsRole(Role::Editor);
        $contact = Contact::factory()->create();

        $this->get(route('filament.admin.resources.contacts.view', $contact))->assertForbidden();
    }
}
