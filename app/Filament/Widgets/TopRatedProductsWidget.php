<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopRatedProductsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Productos Mejor Calificados (Top 5)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->having('reviews_count', '>', 0) // Only products with reviews
                    ->orderByDesc('reviews_avg_rating')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->limit(20)
                    ->tooltip(fn (Product $record): string => $record->name),
                Tables\Columns\TextColumn::make('reviews_avg_rating')
                    ->label('Rating')
                    ->avg('reviews', 'rating') // Fallback/Helper
                    ->formatStateUsing(fn ($state) => number_format($state, 1).' ⭐')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('reviews_count')
                    ->label('Reseñas')
                    ->badge()
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
