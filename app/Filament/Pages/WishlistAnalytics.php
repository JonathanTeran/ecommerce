<?php

namespace App\Filament\Pages;

use App\Enums\Module;
use App\Models\Product;
use App\Models\Wishlist;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WishlistAnalytics extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $title = 'Análisis de Wishlists';

    protected static ?string $navigationLabel = 'Wishlists';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.wishlist-analytics';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            return false;
        }

        return $tenant->hasModule(Module::Products);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getStats(): array
    {
        return [
            'total_wishlisted' => Wishlist::count(),
            'unique_products' => Wishlist::distinct('product_id')->count('product_id'),
            'unique_users' => Wishlist::distinct('user_id')->count('user_id'),
            'out_of_stock_wishlisted' => Wishlist::whereHas('product', fn (Builder $q) => $q->where('quantity', '<=', 0))->count(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->has('wishlists')
                    ->withCount('wishlists')
                    ->with(['category', 'brand'])
            )
            ->defaultSort('wishlists_count', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): string => $record->sku ?? ''),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('brand.name')
                    ->label('Marca')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('wishlists_count')
                    ->label('En Wishlists')
                    ->sortable()
                    ->badge()
                    ->color('danger'),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn (Product $record): string => match (true) {
                        $record->quantity <= 0 => 'danger',
                        $record->quantity <= ($record->low_stock_threshold ?? 5) => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('sales_count')
                    ->label('Ventas')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('conversion')
                    ->label('Conversión')
                    ->state(fn (Product $record): string => $record->wishlists_count > 0
                        ? number_format(($record->sales_count / $record->wishlists_count) * 100, 1) . '%'
                        : '0%')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('out_of_stock')
                    ->label('Agotados')
                    ->query(fn (Builder $query): Builder => $query->where('quantity', '<=', 0)),
                Filter::make('low_stock')
                    ->label('Stock Bajo')
                    ->query(fn (Builder $query): Builder => $query->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'low_stock_threshold')),
                Filter::make('in_stock')
                    ->label('En Stock')
                    ->query(fn (Builder $query): Builder => $query->where('quantity', '>', 0)),
            ]);
    }
}
