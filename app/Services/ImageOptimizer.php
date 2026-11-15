<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Converts uploaded raster images (JPEG/PNG) into resized WebP files.
 * SVG and other formats are stored untouched.
 */
class ImageOptimizer
{
    public const int MAX_WIDTH = 1920;

    public const int QUALITY = 82;

    /**
     * Store the file on the given disk/directory and return its relative path.
     */
    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $mime = (string) $file->getMimeType();

        if (in_array($mime, ['image/svg+xml', 'image/svg'], true) || str_ends_with(strtolower($file->getClientOriginalName()), '.svg')) {
            $this->assertSafeSvg((string) file_get_contents($file->getRealPath()));

            return $file->storeAs($directory, Str::ulid().'.svg', ['disk' => $disk]);
        }

        if (! in_array($mime, ['image/jpeg', 'image/png'], true) || ! function_exists('imagewebp')) {
            return $file->storeAs($directory, Str::ulid().'.'.$file->guessExtension(), ['disk' => $disk]);
        }

        $image = $mime === 'image/png'
            ? @imagecreatefrompng($file->getRealPath())
            : @imagecreatefromjpeg($file->getRealPath());

        if ($image === false) {
            return $file->storeAs($directory, Str::ulid().'.'.$file->guessExtension(), ['disk' => $disk]);
        }

        if (imagesx($image) > self::MAX_WIDTH) {
            $resized = imagescale($image, self::MAX_WIDTH);
            imagedestroy($image);
            $image = $resized;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, self::QUALITY);
        $contents = (string) ob_get_clean();
        imagedestroy($image);

        $path = trim($directory, '/').'/'.Str::ulid().'.webp';
        Storage::disk($disk)->put($path, $contents, ['visibility' => 'public']);

        return $path;
    }

    /**
     * SVG files can embed scripts: refuse anything active (scripts, event handlers, external content).
     *
     * @throws ValidationException
     */
    public function assertSafeSvg(string $contents): void
    {
        $dangerous = '/<\s*(script|foreignObject|iframe|embed|object)\b|\son[a-z]+\s*=|javascript:|data:text\/html|<!ENTITY|xlink:href\s*=\s*["\']\s*(?!#)/i';

        if (! str_contains($contents, '<svg') || preg_match($dangerous, $contents) === 1) {
            throw ValidationException::withMessages([
                'data' => 'Ce fichier SVG contient des éléments non autorisés (scripts, liens externes…).',
            ]);
        }
    }
}
