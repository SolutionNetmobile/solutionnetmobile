<?php

namespace App\Filament\Widgets;

use App\Enums\ContactStatus;
use App\Services\DashboardStatistics;
use Filament\Widgets\ChartWidget;

class ProspectsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Répartition des prospects par statut';

    protected ?string $description = 'Demandes non archivées.';

    protected ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return auth()->user()?->can('contacts.view') ?? false;
    }

    protected function getData(): array
    {
        $counts = app(DashboardStatistics::class)->prospectsByStatus();
        $statuses = ContactStatus::cases();

        return [
            'datasets' => [
                [
                    'data' => array_values($counts),
                    'backgroundColor' => array_map(fn (ContactStatus $status): string => $status->chartColor(), $statuses),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => array_map(fn (ContactStatus $status): string => $status->getLabel(), $statuses),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'right']],
            'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
        ];
    }
}
