<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaasStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $activeTenants = Tenant::where('is_active', true)->count();
        $totalTenants = Tenant::count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $canceledSubscriptions = Subscription::where('status', 'cancelled')->count();
        $totalSubscriptions = Subscription::count();
        
        $mrr = Subscription::where('status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price');
            
        $arr = $mrr * 12;
        
        $churnRate = $totalSubscriptions > 0 
            ? round(($canceledSubscriptions / $totalSubscriptions) * 100, 2) 
            : 0;

        return [
            Stat::make('Tenants Activos', $activeTenants)
                ->description("{$totalTenants} total")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),
            Stat::make('MRR / ARR', '$' . number_format($mrr, 2) . ' / $' . number_format($arr, 2))
                ->description('Ingreso Recurrente (Mensual / Anual)')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            Stat::make('Churn Rate', "{$churnRate}%")
                ->description("Suscripciones Canceladas: {$canceledSubscriptions}")
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color($churnRate > 5 ? 'danger' : 'success'),
            Stat::make('Suscripciones Activas', $activeSubscriptions)
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('info'),
        ];
    }
}
