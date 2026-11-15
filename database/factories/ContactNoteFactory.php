<?php

namespace Database\Factories;

use App\Enums\ContactNoteType;
use App\Models\Contact;
use App\Models\ContactNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactNote>
 */
class ContactNoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'user_id' => null,
            'type' => fake()->randomElement([ContactNoteType::Note, ContactNoteType::Call, ContactNoteType::Email]),
            'body' => fake()->sentence(12),
        ];
    }
}
