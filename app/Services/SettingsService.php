<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Reads and writes the editable site settings stored in `site_settings`.
 * Values are cached and fall back to config('site.defaults').
 */
class SettingsService
{
    public const string CACHE_KEY = 'site_settings.all';

    /**
     * @var array<string, string|null>|null
     */
    private ?array $loaded = null;

    public function get(string $key, mixed $default = null): mixed
    {
        $values = $this->all();

        if (array_key_exists($key, $values) && filled($values[$key])) {
            return $values[$key];
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
        return $this->loaded ??= array_merge(
            $this->defaults(),
            Cache::rememberForever(self::CACHE_KEY, fn (): array => SiteSetting::query()->pluck('value', 'key')->all()),
        );
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
        $this->loaded = null;
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
