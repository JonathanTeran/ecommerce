<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Ingresos por Período';

    protected static ?int $sort = 2;

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

        $trend = \Flowframe\Trend\Trend::query(
            Order::query()->where('payment_status', \App\Enums\PaymentStatus::COMPLETED)
        );

        $data = match ($activeFilter) {
            'today' => $trend
                ->between(
                    start: now()->startOfDay(),
                    end: now()->endOfDay(),
                )
                ->perHour()
                ->sum('total'),
            'week' => $trend
                ->between(
                    start: now()->startOfWeek(),
                    end: now()->endOfWeek(),
                )
                ->perDay()
                ->sum('total'),
            'month' => $trend
                ->between(
                    start: now()->startOfMonth(),
                    end: now()->endOfMonth(),
                )
                ->perDay()
                ->sum('total'),
            'year' => $trend
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear(),
                )
                ->perMonth()
                ->sum('total'),
        };

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $data->map(fn (\Flowframe\Trend\TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                    'borderColor' => '#10b981', // Emerald
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
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
