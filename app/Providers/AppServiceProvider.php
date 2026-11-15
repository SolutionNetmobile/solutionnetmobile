<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\User;
use App\Services\PublicContentService;
use App\Services\SettingsService;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(PublicContentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // The super administrator implicitly holds every permission (if active).
        Gate::before(function (User $user): ?bool {
            return $user->is_active && $user->hasRole(Role::SuperAdmin->value) ? true : null;
        });

        RateLimiter::for('contact', function (Request $request): array {
            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perDay(20)->by($request->ip()),
            ];
        });

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });
    }
}
