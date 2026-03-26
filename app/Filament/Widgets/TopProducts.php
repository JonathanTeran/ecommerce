<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProducts extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Productos Más Vendidos';

    protected static ?string $pollingInterval = '10s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->withCount(['orderItems as total_sold' => function (Builder $query) {
                        $query->select(\Illuminate\Support\Facades\DB::raw('sum(quantity)'));
                    }])
                    ->orderByDesc('total_sold')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('total_sold')
                    ->label('Unidades Vendidas')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
            ])
            ->paginated(false);
    }
}
