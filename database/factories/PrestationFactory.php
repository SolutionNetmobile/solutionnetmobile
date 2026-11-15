<?php

namespace Database\Factories;

use App\Models\Prestation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Prestation>
 */
class PrestationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(3), '.');

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(14),
            'content' => '<p>'.fake()->paragraph(5).'</p>',
            'icon' => fake()->randomElement(array_keys(config('site.icons'))),
            'image' => null,
            'sort_order' => fake()->numberBetween(1, 50),
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => ['is_published' => false]);
    }
}
