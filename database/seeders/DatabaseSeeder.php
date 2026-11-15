<?php

namespace Database\Seeders;

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

        if (! app()->isProduction()) {
            $this->call(DemoContactSeeder::class);
        }
    }
}
