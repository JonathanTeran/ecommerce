<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Estado de Pedidos';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $data = Order::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#f59e0b', // warning (pending)
                        '#3b82f6', // primary (processing)
                        '#10b981', // success (delivered)
                        '#ef4444', // danger (cancelled)
                        '#6b7280', // gray (others)
                    ],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
