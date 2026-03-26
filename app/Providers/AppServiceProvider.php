<?php

namespace App\Providers;

use App\Listeners\AuditAuthEvents;
use App\Listeners\MergeGuestCart;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Observers\BrandObserver;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance('current_tenant', null);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
        Brand::observe(BrandObserver::class);

        Event::listen(
            Login::class,
            MergeGuestCart::class,
        );

        Event::listen(Login::class, [AuditAuthEvents::class, 'handleLogin']);
        Event::listen(Logout::class, [AuditAuthEvents::class, 'handleLogout']);

        Event::listen(\Lab404\Impersonate\Events\TakeImpersonation::class, function ($event) {
            activity('impersonation')
                ->causedBy($event->impersonator)
                ->performedOn($event->impersonated)
                ->withProperties([
                    'impersonator_email' => $event->impersonator->email,
                    'impersonated_email' => $event->impersonated->email,
                    'ip' => request()->ip(),
                ])
                ->log("Impersonacion iniciada: {$event->impersonator->name} → {$event->impersonated->name}");
        });

        Event::listen(\Lab404\Impersonate\Events\LeaveImpersonation::class, function ($event) {
            activity('impersonation')
                ->causedBy($event->impersonator)
                ->performedOn($event->impersonated)
                ->log("Impersonacion finalizada: {$event->impersonator->name} ← {$event->impersonated->name}");
        });

        View::composer('*', function ($view) {
            if (! $view->offsetExists('currentTenant')) {
                $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
                $view->with('currentTenant', $tenant);
            }

            if (! $view->offsetExists('tenantSettings')) {
                $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
                $settings = $tenant?->generalSettings;
                $view->with('tenantSettings', $settings);
            }
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('quotation', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}
