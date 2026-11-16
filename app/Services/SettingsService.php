<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Reads and writes the editable site settings stored in `site_settings`.
 * Values are cached and fall back to config('site.defaults') when never saved.
 */
class SettingsService
{
    public const string CACHE_KEY = 'site_settings.all';

    /**
     * @var array<string, string|null>|null
     */
    private ?array $stored = null;

    /**
     * A setting saved from the back office wins, even when cleared; the
     * config default only applies to settings that were never saved.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $stored = $this->stored();

        if (array_key_exists($key, $stored)) {
            return filled($stored[$key]) ? $stored[$key] : $default;
        }

        return $default ?? $this->defaults()[$key] ?? null;
    }

    /**
     * Public URL of a stored file setting (logo, favicon, images…).
     */
    public function url(string $key, ?string $fallback = null): ?string
    {
        $path = $this->get($key);

        if (blank($path)) {
            return $fallback;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Notification recipients for the contact form.
     *
     * @return array<int, string>
     */
    public function notificationEmails(): array
    {
        $raw = (string) $this->get('notification_emails', $this->get('contact_email'));

        return collect(preg_split('/[\s,;]+/', $raw))
            ->filter(fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }

    public function retentionMonths(): int
    {
        return max(1, (int) $this->get('contact_retention_months', config('site.contact_retention_months')));
    }

    /**
     * All stored values, merged over the defaults.
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return array_merge($this->defaults(), $this->stored());
    }

    /**
     * Values of one group, keyed by setting name.
     *
     * @return array<string, string|null>
     */
    public function group(string $group): array
    {
        return array_intersect_key($this->all(), config("site.defaults.{$group}", []));
    }

    /**
     * Persist several settings at once.
     *
     * @param  array<string, mixed>  $values
     */
    public function set(array $values): void
    {
        $groups = $this->groupOfKeys();

        foreach ($values as $key => $value) {
            if (! isset($groups[$key])) {
                continue;
            }

            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['group' => $groups[$key], 'value' => is_array($value) ? json_encode($value) : $value],
            );
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->stored = null;
    }

    /**
     * Values saved in the database, keyed by setting name.
     *
     * @return array<string, string|null>
     */
    private function stored(): array
    {
        return $this->stored ??= Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => SiteSetting::query()->pluck('value', 'key')->all(),
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function defaults(): array
    {
        return array_merge(...array_values(config('site.defaults', [])));
    }

    /**
     * @return array<string, string>
     */
    private function groupOfKeys(): array
    {
        $map = [];

        foreach (config('site.defaults', []) as $group => $keys) {
            foreach (array_keys($keys) as $key) {
                $map[$key] = $group;
            }
        }

        return $map;
    }
}
