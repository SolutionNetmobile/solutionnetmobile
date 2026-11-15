<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitizes rich text written in the back office before it is rendered unescaped.
 */
final class Html
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function clean(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        return self::sanitizer()->sanitize($html);
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->allowMediaSchemes(['https', 'http'])
                ->allowRelativeMedias()
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                ->withMaxInputLength(500_000)
        );
    }
}
