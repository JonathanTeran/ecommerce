<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $completedOrders = Order::withoutGlobalScopes()
            ->where('payment_status', PaymentStatus::COMPLETED);

        $totalRevenue = (clone $completedOrders)->sum('total');

        $monthlyOrders = Order::withoutGlobalScopes()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        $monthlyRevenue = (clone $monthlyOrders)
            ->where('payment_status', PaymentStatus::COMPLETED)
            ->sum('total');

        $averageTicket = (clone $completedOrders)->avg('total') ?? 0;

        $monthlyOrderCount = (clone $monthlyOrders)->count();

        return [
            Stat::make('Ingresos Totales', '$' . number_format($totalRevenue, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->description('Total acumulado')
                ->color('success'),

            Stat::make('Ingresos del Mes', '$' . number_format($monthlyRevenue, 2))
                ->descriptionIcon('heroicon-m-calendar')
                ->description(now()->translatedFormat('F Y'))
                ->color('info'),

            Stat::make('Ticket Promedio', '$' . number_format($averageTicket, 2))
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->description('Ordenes completadas')
                ->color('warning'),

            Stat::make('Ordenes del Mes', number_format($monthlyOrderCount))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->description(now()->translatedFormat('F Y'))
                ->color('primary'),
        ];
    }
}
