<?php

namespace Database\Factories;

use App\Models\Methodology;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Methodology>
 */
class MethodologyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ucfirst(fake()->word()),
            'description' => fake()->sentence(5),
            'icon' => fake()->randomElement(array_keys(config('site.icons'))),
            'sort_order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
