<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatistics;
use Filament\Widgets\ChartWidget;

class ContactsTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Évolution des demandes de contact';

    protected ?string $description = 'Nombre de demandes reçues sur la période.';

    protected ?string $maxHeight = '280px';

    public ?string $filter = 'year';

    public static function canView(): bool
    {
        return auth()->user()?->can('contacts.view') ?? false;
    }

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            'week' => '7 derniers jours',
            'month' => '30 derniers jours',
            'year' => '12 derniers mois',
        ];
    }

    protected function getData(): array
    {
        $series = app(DashboardStatistics::class)->requestsOverPeriod($this->filter ?? 'year');

        return [
            'datasets' => [
                [
                    'label' => 'Demandes',
                    'data' => $series['values'],
                    'borderColor' => '#0755C8',
                    'backgroundColor' => 'rgba(7, 85, 200, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
