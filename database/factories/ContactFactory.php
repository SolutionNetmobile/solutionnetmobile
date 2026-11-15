<?php

namespace Database\Factories;

use App\Enums\Budget;
use App\Enums\ContactStatus;
use App\Enums\ProjectType;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company' => fake()->optional(0.8)->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.7)->numerify('06 ## ## ## ##'),
            'project_type' => fake()->randomElement(ProjectType::cases()),
            'budget' => fake()->optional()->randomElement(Budget::cases()),
            'message' => fake()->paragraph(3),
            'privacy_accepted_at' => now(),
            'status' => ContactStatus::New,
        ];
    }

    public function status(ContactStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => ['archived_at' => now()]);
    }
}
