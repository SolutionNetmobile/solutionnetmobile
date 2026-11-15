<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
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
            'content' => '<p>'.fake()->paragraph(6).'</p>',
            'status' => PageStatus::Published,
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => PageStatus::Draft, 'published_at' => null]);
    }
}
