<?php

use App\Services\SettingsService;

if (! function_exists('site')) {
    /**
     * Access the editable site settings from anywhere (Blade views included).
     */
    function site(): SettingsService
    {
        return app(SettingsService::class);
    }
}
