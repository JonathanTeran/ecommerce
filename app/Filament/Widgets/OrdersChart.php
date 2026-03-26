<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Pedidos por Período';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 1;

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hoy',
            'week' => 'Esta Semana',
            'month' => 'Este Mes',
            'year' => 'Este Año',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $query = Order::query();

        $data = match ($activeFilter) {
            'today' => \Flowframe\Trend\Trend::model(Order::class)
                ->between(
                    start: now()->startOfDay(),
                    end: now()->endOfDay(),
                )
                ->perHour()
                ->count(),
            'week' => \Flowframe\Trend\Trend::model(Order::class)
                ->between(
                    start: now()->startOfWeek(),
                    end: now()->endOfWeek(),
                )
                ->perDay()
                ->count(),
            'month' => \Flowframe\Trend\Trend::model(Order::class)
                ->between(
                    start: now()->startOfMonth(),
                    end: now()->endOfMonth(),
                )
                ->perDay()
                ->count(),
            'year' => \Flowframe\Trend\Trend::model(Order::class)
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear(),
                )
                ->perMonth()
                ->count(),
        };

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos',
                    'data' => $data->map(fn (\Flowframe\Trend\TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
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
