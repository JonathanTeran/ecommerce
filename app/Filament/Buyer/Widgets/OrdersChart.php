<?php

namespace App\Filament\Buyer\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Historial de Compras';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = \Flowframe\Trend\Trend::model(Order::class)
            ->query(
                Order::where('user_id', Auth::id())
                    ->whereIn('payment_status', ['completed', 'pending']) // Include pending for visibility
                    ->whereNotIn('status', ['cancelled'])
            )
            ->between(
                start: now()->subMonths(6),
                end: now(),
            )
            ->perMonth()
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label' => 'Gastos ($)', // Localized label
                    'data' => $data->map(fn (\Flowframe\Trend\TrendValue $value) => $value->aggregate),
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $data->map(fn (\Flowframe\Trend\TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
