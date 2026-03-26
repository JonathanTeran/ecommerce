<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryMovementResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Inventory;
    }

    protected static ?string $model = InventoryMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Kardex';

    protected static ?string $modelLabel = 'Movimiento';

    protected static ?string $pluralModelLabel = 'Kardex';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->label('Tenant')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->columnSpanFull(),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('type_label')
                    ->label('Tipo de Movimiento')
                    ->disabled(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad')
                    ->disabled(),
                Forms\Components\TextInput::make('unit_cost')
                    ->label('Costo Unitario')
                    ->prefix('$')
                    ->disabled(),
                Forms\Components\TextInput::make('total_cost')
                    ->label('Costo Total')
                    ->prefix('$')
                    ->disabled(),
                Forms\Components\TextInput::make('balance_quantity')
                    ->label('Saldo (Cantidad)')
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('created_at')
                    ->label('Fecha')
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
                    ->searchable()
                    ->label('Producto')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type_label')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Saldo Inicial' => 'gray',
                        'Compra (Ingreso)' => 'success',
                        'Venta (Egreso)' => 'warning',
                        'Devolución' => 'info',
                        'Ajuste (+)' => 'success',
                        'Ajuste (-)' => 'danger',
                        'Transferencia (Salida)' => 'warning',
                        'Transferencia (Entrada)' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->sortable()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Costo Unit.')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Costo Total')
                    ->money('USD')
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('balance_quantity')
                    ->label('Saldo')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('warehouseLocation.name')
                    ->label('Ubicación')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Referencia')
                    ->formatStateUsing(fn ($state, $record) => $record->reference ? class_basename($record->reference_type) . ' #' . $record->reference_id : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->label('Producto'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'initial_balance' => 'Saldo Inicial',
                        'purchase' => 'Compra',
                        'sale' => 'Venta',
                        'return' => 'Devolución',
                        'adjustment_inc' => 'Ajuste (+)',
                        'adjustment_dec' => 'Ajuste (-)',
                        'transfer_out' => 'Transferencia (Salida)',
                        'transfer_in' => 'Transferencia (Entrada)',
                    ])
                    ->label('Tipo'),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    })
                    ->label('Rango de Fechas'),
                Tables\Filters\SelectFilter::make('warehouse_location')
                    ->relationship('warehouseLocation', 'name')
                    ->searchable()
                    ->label('Ubicación'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->headerActions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryMovements::route('/'),
            'view' => Pages\ViewInventoryMovement::route('/{record}'),
        ];
    }
}
