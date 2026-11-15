<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Copy the default settings (config/site.php) into the database without
     * overwriting values already edited from the back office.
     */
    public function run(SettingsService $settings): void
    {
        foreach (config('site.defaults') as $group => $values) {
            foreach ($values as $key => $value) {
                SiteSetting::query()->firstOrCreate(
                    ['key' => $key],
                    ['group' => $group, 'value' => $value],
                );
            }
        }

        $settings->flush();
    }
}
