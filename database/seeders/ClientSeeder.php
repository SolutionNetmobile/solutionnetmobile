<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ClientSeeder extends Seeder
{
    /**
     * Demonstration client logos (placeholders, as on the mock-up).
     */
    public function run(): void
    {
        $clients = [
            ['name' => 'Client Alpha', 'file' => 'client-alpha.svg'],
            ['name' => 'Client Orbit', 'file' => 'client-orbit.svg'],
            ['name' => 'Client Summit', 'file' => 'client-summit.svg'],
            ['name' => 'Client Block', 'file' => 'client-block.svg'],
        ];

        foreach ($clients as $index => $client) {
            $path = 'clients/'.$client['file'];
            $source = database_path('seeders/assets/'.$client['file']);

            if (! Storage::disk('public')->exists($path) && File::exists($source)) {
                Storage::disk('public')->put($path, File::get($source));
            }

            Client::query()->updateOrCreate(
                ['name' => $client['name']],
                [
                    'logo' => $path,
                    'description' => 'Logo de démonstration — à remplacer depuis l’administration.',
                    'website_url' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
