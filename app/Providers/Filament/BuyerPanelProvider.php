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
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class BuyerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('buyer')
            ->path('buyer')
            ->login(\App\Filament\Buyer\Pages\Auth\Login::class)
            ->registration(\App\Filament\Buyer\Pages\Auth\Register::class)
            ->passwordReset()
            ->emailVerification()
            ->profile(\App\Filament\Buyer\Pages\EditProfile::class)
            ->brandLogo(fn () => ($logo = \App\Models\GeneralSetting::first()?->site_logo) ? asset('storage/'.$logo) : null)
            ->brandName(fn () => \App\Models\GeneralSetting::first()?->site_name ?? config('app.name'))
            ->brandLogoHeight('3rem')
            ->colors([
                'primary' => Color::Zinc, // Premium feeling
            ])
            ->topNavigation() // Better for buyers
            ->discoverResources(in: app_path('Filament/Buyer/Resources'), for: 'App\\Filament\\Buyer\\Resources')
            ->discoverPages(in: app_path('Filament/Buyer/Pages'), for: 'App\\Filament\\Buyer\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Buyer/Widgets'), for: 'App\\Filament\\Buyer\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Buyer\Widgets\BuyerStatsOverview::class,
                \App\Filament\Buyer\Widgets\OrdersChart::class,
                \App\Filament\Buyer\Widgets\RecentOrdersWidget::class,
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
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => Blade::render('<x-social-login-buttons />'),
            );
    }
}
