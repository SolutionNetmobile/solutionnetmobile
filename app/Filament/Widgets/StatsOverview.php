<?php

namespace App\Filament\Widgets;

use App\Enums\ContactStatus;
use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\Prestations\PrestationResource;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Prestation;
use App\Services\DashboardStatistics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $stats = [];
        $user = auth()->user();

        if ($user?->can('contacts.view')) {
            $thisMonth = Contact::query()->where('created_at', '>=', now()->startOfMonth())->count();

            $stats[] = Stat::make('Demandes de contact', Contact::query()->count())
                ->description("{$thisMonth} ce mois-ci")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart(app(DashboardStatistics::class)->lastSevenDays())
                ->color('primary')
                ->url(ContactResource::getUrl('index'));

            $stats[] = Stat::make('Nouveaux prospects', Contact::query()->notArchived()->where('status', ContactStatus::New)->count())
                ->description('À qualifier')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('info')
                ->url(ContactResource::getUrl('index', ['tab' => 'new']));

            $stats[] = Stat::make('Prospects en cours', Contact::query()->notArchived()->whereIn('status', ContactStatus::open())->count())
                ->description('À contacter, en cours, devis envoyé')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('warning')
                ->url(ContactResource::getUrl('index', ['tab' => 'open']));
        }

        if ($user?->can('prestations.view')) {
            $stats[] = Stat::make('Prestations actives', Prestation::query()->published()->count())
                ->description(Prestation::query()->count().' au total')
                ->descriptionIcon('heroicon-m-briefcase')
                ->url(PrestationResource::getUrl('index'));
        }

        if ($user?->can('clients.view')) {
            $stats[] = Stat::make('Références clients', Client::query()->active()->count())
                ->description('Logos affichés sur le site')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->url(ClientResource::getUrl('index'));
        }

        return $stats;
    }
}
