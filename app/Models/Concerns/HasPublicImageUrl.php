<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves a stored file path (public disk) into a public URL.
 */
trait HasPublicImageUrl
{
    protected function publicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
