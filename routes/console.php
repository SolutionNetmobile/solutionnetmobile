<?php

use App\Services\ContactService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('contacts:purge', function (ContactService $contacts): void {
    $deleted = $contacts->purgeExpired();

    $this->info("{$deleted} demande(s) de contact supprimée(s) (durée de conservation dépassée).");
})->purpose('Supprime définitivement les demandes de contact dont la durée de conservation RGPD est dépassée');

Schedule::command('contacts:purge')->dailyAt('03:00');
