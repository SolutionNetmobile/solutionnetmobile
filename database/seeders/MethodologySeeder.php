<?php

namespace Database\Seeders;

use App\Models\Methodology;
use Illuminate\Database\Seeder;

class MethodologySeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            ['title' => 'Comprendre', 'description' => 'Vos enjeux et vos objectifs', 'icon' => 'search'],
            ['title' => 'Définir', 'description' => 'La solution la plus adaptée', 'icon' => 'lightbulb'],
            ['title' => 'Produire', 'description' => 'Votre site web ou application mobile', 'icon' => 'code-xml'],
            ['title' => 'Accompagner', 'description' => 'Le déploiement et la réussite', 'icon' => 'rocket'],
        ];

        foreach ($steps as $index => $step) {
            Methodology::query()->updateOrCreate(
                ['title' => $step['title']],
                $step + ['sort_order' => $index + 1, 'is_active' => true],
            );
        }
    }
}
