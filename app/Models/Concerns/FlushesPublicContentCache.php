<?php

namespace App\Models\Concerns;

use App\Services\PublicContentService;

/**
 * Flushes the cached public content whenever a model using this trait changes.
 */
trait FlushesPublicContentCache
{
    public static function bootFlushesPublicContentCache(): void
    {
        $flush = fn (): mixed => app(PublicContentService::class)->flush();

        static::saved($flush);
        static::deleted($flush);

        if (method_exists(static::class, 'restored')) {
            static::restored($flush);
        }
    }
}
