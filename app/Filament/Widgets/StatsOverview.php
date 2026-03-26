<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        // Revenue comparison
        $revenueThisMonth = \App\Models\Order::where('created_at', '>=', now()->startOfMonth())->sum('total');
        $revenueLastMonth = \App\Models\Order::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->sum('total');

        $revenueDiff = $revenueThisMonth - $revenueLastMonth;
        $revenueGrowth = $revenueLastMonth > 0 ? ($revenueDiff / $revenueLastMonth) * 100 : ($revenueThisMonth > 0 ? 100 : 0);
        $revenueIcon = $revenueDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $revenueColor = $revenueDiff >= 0 ? 'success' : 'danger';

        // Orders comparison
        $ordersThisMonth = \App\Models\Order::where('created_at', '>=', now()->startOfMonth())->count();
        $ordersLastMonth = \App\Models\Order::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->count();

        $ordersDiff = $ordersThisMonth - $ordersLastMonth;
        $ordersGrowth = $ordersLastMonth > 0 ? ($ordersDiff / $ordersLastMonth) * 100 : ($ordersThisMonth > 0 ? 100 : 0);
        $ordersIcon = $ordersDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $ordersColor = $ordersDiff >= 0 ? 'success' : 'danger';

        return [
            Stat::make('Ingresos (Este Mes)', '$'.number_format($revenueThisMonth, 2))
                ->description(($revenueGrowth >= 0 ? '+' : '').number_format($revenueGrowth, 1).'% vs mes anterior')
                ->descriptionIcon($revenueIcon)
                ->chart([$revenueLastMonth, $revenueThisMonth])
                ->color($revenueColor),

            Stat::make('Pedidos (Este Mes)', $ordersThisMonth)
                ->description(($ordersGrowth >= 0 ? '+' : '').number_format($ordersGrowth, 1).'% vs mes anterior')
                ->descriptionIcon($ordersIcon)
                ->chart([$ordersLastMonth, $ordersThisMonth])
                ->color($ordersColor),

            Stat::make('Ticket Promedio', '$'.number_format(\App\Models\Order::avg('total') ?? 0, 2))
                ->description('Valor histórico promedio')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
        ];
    }
}
