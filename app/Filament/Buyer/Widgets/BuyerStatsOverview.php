<?php

namespace App\Filament\Buyer\Widgets;

use App\Models\Order;
use App\Models\Wishlist;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class BuyerStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        // 1. Total Orders
        $totalOrders = Order::where('user_id', $user->id)->count();

        // 2. Pending Orders
        $pendingOrders = Order::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'shipped'])
            ->count();

        // 3. Total Spent (Including Pending for immediate feedback)
        $totalSpent = Order::where('user_id', $user->id)
            ->whereIn('payment_status', ['completed', 'pending']) // Include pending
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        // 4. Wishlist items count
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();

        return [
            Stat::make('Total Pedidos', $totalOrders)
                ->description('Todos los pedidos')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            Stat::make('Total Gastado', '$' . number_format($totalSpent, 2))
                ->description('Valor acumulado')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Pedidos Pendientes', $pendingOrders)
                ->description('En proceso / enviados')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),

            Stat::make('Lista de Deseos', $wishlistCount)
                ->description('Productos guardados')
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),
        ];
    }
}
