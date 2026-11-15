<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with the mock-up content.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
            PrestationSeeder::class,
            MethodologySeeder::class,
            ClientSeeder::class,
            PageSeeder::class,
        ]);

        // Demo prospects rely on Faker, a dev dependency absent from servers (composer install --no-dev).
        if (! app()->isProduction() && class_exists(Factory::class)) {
            $this->call(DemoContactSeeder::class);
        }
    }
}
