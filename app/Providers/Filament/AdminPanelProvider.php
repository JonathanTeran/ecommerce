<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->plugins([
                //
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn (): string => view('filament.hooks.language-switch')
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<script>window.__CURRENCY_SYMBOL__ = ' . json_encode(\App\Models\GeneralSetting::getCurrencySymbol()) . ';</script>'
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::SCRIPTS_AFTER,
                fn (): string => view('filament.hooks.sidebar-scroll-persist')->render()
            )
            ->login()
            ->colors(function () {
                $color = \App\Models\GeneralSetting::first()?->theme_color ?? 'indigo';

                return [
                    'primary' => match ($color) {
                        'amber' => Color::Amber,
                        'emerald' => Color::Emerald,
                        'red' => Color::Red,
                        'blue' => Color::Blue,
                        'slate' => Color::Slate,
                        default => Color::Indigo,
                    },
                    'gray' => Color::Slate,
                ];
            })
            ->font('Outfit')
            ->brandName(fn () => \App\Models\GeneralSetting::first()?->site_name ?? config('app.name'))
            ->brandLogo(fn () => ($logo = \App\Models\GeneralSetting::first()?->site_logo) ? asset('storage/'.$logo) : null)
            ->brandLogoHeight('3rem')
            ->favicon(fn () => ($favicon = \App\Models\GeneralSetting::first()?->site_favicon) ? asset('storage/'.$favicon) : null)
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->unsavedChangesAlerts()
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\RevenueChart::class,
                \App\Filament\Widgets\OrdersChart::class,
                \App\Filament\Widgets\TopProducts::class,
                \App\Filament\Widgets\OrdersByStatusChart::class,
                \App\Filament\Widgets\UserSignupsChart::class,
                \App\Filament\Widgets\TopRatedProductsWidget::class,
                \App\Filament\Widgets\LowestRatedProductsWidget::class,
                \App\Filament\Widgets\LatestOrders::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                \App\Http\Middleware\LanguageMiddleware::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\ResolveTenant::class,
            ]);
    }
}
