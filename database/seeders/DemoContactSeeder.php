<?php

namespace Database\Seeders;

use App\Enums\ContactNoteType;
use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Models\ContactNote;
use Illuminate\Database\Seeder;

class DemoContactSeeder extends Seeder
{
    /**
     * Demonstration prospects spread over the last six months (non-production only).
     */
    public function run(): void
    {
        if (Contact::query()->exists()) {
            return;
        }

        $statuses = [
            ContactStatus::New, ContactStatus::New, ContactStatus::New,
            ContactStatus::ToContact, ContactStatus::ToContact,
            ContactStatus::InProgress, ContactStatus::InProgress,
            ContactStatus::QuoteSent, ContactStatus::Won, ContactStatus::Lost,
        ];

        for ($i = 0; $i < 28; $i++) {
            $createdAt = now()->subDays(random_int(0, 180))->setTime(random_int(8, 19), random_int(0, 59));

            $contact = Contact::factory()
                ->status($statuses[array_rand($statuses)])
                ->create(['created_at' => $createdAt, 'updated_at' => $createdAt, 'privacy_accepted_at' => $createdAt]);

            if ($contact->status !== ContactStatus::New) {
                ContactNote::factory()->for($contact)->create([
                    'type' => ContactNoteType::Call,
                    'body' => 'Premier échange téléphonique : besoin confirmé, rappel prévu.',
                    'created_at' => $createdAt->copy()->addDays(2),
                ]);
            }
        }
    }
}
