<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName('Solution Netmobile')
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight('2.6rem')
            ->favicon(asset('favicon.svg'))
            ->font('Outfit')
            ->colors([
                'primary' => Color::hex(config('site.brand.primary')),
                'info' => Color::hex(config('site.brand.secondary')),
                'gray' => Color::Slate,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('7xl')
            ->databaseTransactions()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups([
                NavigationGroup::make('Prospects'),
                NavigationGroup::make('Contenus du site'),
                NavigationGroup::make('Paramètres'),
                NavigationGroup::make('Administration'),
            ])
            ->navigationItems([
                NavigationItem::make('Voir le site')
                    ->url(fn (): string => route('home'), shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->sort(99),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
