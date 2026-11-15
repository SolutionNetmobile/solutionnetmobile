<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatistics;
use Filament\Widgets\ChartWidget;

class ContactsByWeekdayChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Répartition des demandes par période';

    protected ?string $description = 'Jour de réception des demandes (12 derniers mois).';

    protected ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return auth()->user()?->can('contacts.view') ?? false;
    }

    protected function getData(): array
    {
        $series = app(DashboardStatistics::class)->requestsByWeekday();

        return [
            'datasets' => [
                [
                    'label' => 'Demandes',
                    'data' => $series['values'],
                    'backgroundColor' => '#1674DB',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
