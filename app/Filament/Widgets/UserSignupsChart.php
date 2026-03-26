<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class UserSignupsChart extends ChartWidget
{
    protected static ?string $heading = 'Nuevos Usuarios (Últimos 3 Meses)';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $data = Trend::model(User::class)
            ->between(
                start: now()->subMonths(3),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Usuarios Registrados',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#8b5cf6', // Violet
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
