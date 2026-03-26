<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\StockAlertResource\Pages;
use App\Models\StockAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockAlertResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Inventory;
    }

    protected static ?string $model = StockAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Alertas de Stock';

    protected static ?string $modelLabel = 'Alerta de Stock';

    protected static ?string $pluralModelLabel = 'Alertas de Stock';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $count = StockAlert::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Producto')
                    ->searchable()
                    ->preload()
                    ->disabled(),
                Forms\Components\TextInput::make('type_label')
                    ->label('Tipo')
                    ->disabled(),
                Forms\Components\TextInput::make('threshold')
                    ->label('Umbral')
                    ->disabled(),
                Forms\Components\TextInput::make('current_quantity')
                    ->label('Cantidad al Momento')
                    ->disabled(),
                Forms\Components\TextInput::make('status_label')
                    ->label('Estado')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Fecha'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type_label')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Stock Bajo' => 'warning',
                        'Agotado' => 'danger',
                        'Sobrestock' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('threshold')
                    ->label('Umbral')
                    ->numeric(),
                Tables\Columns\TextColumn::make('current_quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->color(fn (int $state): string => $state <= 0 ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('status_label')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendiente' => 'danger',
                        'Reconocida' => 'warning',
                        'Resuelta' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('acknowledgedByUser.name')
                    ->label('Reconocida por')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('acknowledged_at')
                    ->dateTime()
                    ->label('Reconocida el')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'low_stock' => 'Stock Bajo',
                        'out_of_stock' => 'Agotado',
                        'overstock' => 'Sobrestock',
                    ])
                    ->label('Tipo'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'acknowledged' => 'Reconocida',
                        'resolved' => 'Resuelta',
                    ])
                    ->label('Estado'),
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->label('Producto'),
            ])
            ->actions([
                Tables\Actions\Action::make('acknowledge')
                    ->label('Reconocer')
                    ->icon('heroicon-o-check')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (StockAlert $record) => $record->acknowledge())
                    ->visible(fn (StockAlert $record): bool => $record->status === 'pending'),
                Tables\Actions\Action::make('resolve')
                    ->label('Resolver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (StockAlert $record) => $record->resolve())
                    ->visible(fn (StockAlert $record): bool => in_array($record->status, ['pending', 'acknowledged'])),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockAlerts::route('/'),
            'view' => Pages\ViewStockAlert::route('/{record}'),
        ];
    }
}
