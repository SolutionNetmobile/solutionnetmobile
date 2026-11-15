<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Methodology;
use App\Models\Prestation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Cached access to the content displayed on the public site.
 *
 * Only raw attribute arrays are cached (the cache store refuses to unserialize
 * objects, see config/cache.php "serializable_classes"); models are re-hydrated
 * on read. The cache is flushed automatically when a related model changes.
 */
class PublicContentService
{
    private const array KEYS = [
        'public.prestations',
        'public.methodologies',
        'public.clients',
    ];

    /**
     * @return Collection<int, Prestation>
     */
    public function prestations(): Collection
    {
        return $this->remember('public.prestations', Prestation::query()->published()->ordered());
    }

    /**
     * @return Collection<int, Methodology>
     */
    public function methodologies(): Collection
    {
        return $this->remember('public.methodologies', Methodology::query()->active()->ordered());
    }

    /**
     * @return Collection<int, Client>
     */
    public function clients(): Collection
    {
        return $this->remember('public.clients', Client::query()->active()->ordered());
    }

    public function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
        }
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Collection<int, TModel>
     */
    private function remember(string $key, Builder $query): Collection
    {
        $rows = Cache::rememberForever(
            $key,
            fn (): array => $query->get()->map(fn (Model $model): array => $model->getAttributes())->all(),
        );

        return $query->getModel()->newQuery()->hydrate($rows);
    }
}
