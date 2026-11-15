<?php

namespace App\Services;

use App\Enums\ContactStatus;
use App\Models\Contact;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Aggregates prospect statistics for the back-office dashboard (portable SQL + PHP grouping).
 */
class DashboardStatistics
{
    /**
     * Number of requests per bucket over a period.
     *
     * @param  'week'|'month'|'year'  $period
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function requestsOverPeriod(string $period): array
    {
        $now = CarbonImmutable::now();

        [$start, $interval, $keyFormat, $labelFormat] = match ($period) {
            'week' => [$now->subDays(6)->startOfDay(), '1 day', 'Y-m-d', 'D d'],
            'month' => [$now->subDays(29)->startOfDay(), '1 day', 'Y-m-d', 'd/m'],
            default => [$now->subMonths(11)->startOfMonth(), '1 month', 'Y-m', 'M Y'],
        };

        $counts = $this->createdDatesSince($start)
            ->countBy(fn (CarbonImmutable $date): string => $date->format($keyFormat));

        $labels = [];
        $values = [];

        foreach (CarbonPeriod::create($start, $interval, $now) as $date) {
            $labels[] = ucfirst($date->translatedFormat($labelFormat));
            $values[] = (int) ($counts[$date->format($keyFormat)] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Requests of the last 12 months grouped by weekday (Monday first).
     *
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function requestsByWeekday(): array
    {
        $counts = $this->createdDatesSince(CarbonImmutable::now()->subYear())
            ->countBy(fn (CarbonImmutable $date): int => $date->dayOfWeekIso);

        $labels = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        return [
            'labels' => $labels,
            'values' => array_map(fn (int $day): int => (int) ($counts[$day] ?? 0), range(1, 7)),
        ];
    }

    /**
     * @return array<string, int> status value => count (non-archived)
     */
    public function prospectsByStatus(): array
    {
        $counts = Contact::query()
            ->notArchived()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(ContactStatus::cases())
            ->mapWithKeys(fn (ContactStatus $status): array => [$status->value => (int) ($counts[$status->value] ?? 0)])
            ->all();
    }

    /**
     * Daily counts for the last 7 days (sparkline).
     *
     * @return array<int, int>
     */
    public function lastSevenDays(): array
    {
        return $this->requestsOverPeriod('week')['values'];
    }

    /**
     * @return Collection<int, CarbonImmutable>
     */
    private function createdDatesSince(CarbonImmutable $start): Collection
    {
        return Contact::query()
            ->where('created_at', '>=', $start)
            ->toBase()
            ->pluck('created_at')
            ->map(fn (string $date): CarbonImmutable => CarbonImmutable::parse($date));
    }
}
