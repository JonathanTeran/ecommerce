<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'InventoryMovements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Read-only view mainly
                Forms\Components\TextInput::make('type_label')
                    ->label('Tipo'),
                Forms\Components\TextInput::make('quantity'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Fecha'),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('adjust_stock')
                    ->label('Ajustar Stock Manualmente')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Ajuste')
                            ->options([
                                'purchase' => 'Compra (Ingreso)',
                                'return' => 'Devolución (Ingreso)',
                                'adjustment_inc' => 'Ajuste Positivo (+)',
                                'adjustment_dec' => 'Ajuste Negativo (-)',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        Forms\Components\TextInput::make('unit_cost')
                            ->label('Costo Unitario (Opcional)')
                            ->numeric()
                            ->default(0)
                            ->hint('Si es 0, se usará el costo actual del producto para salidas. Para ingresos, define el nuevo costo promedio o de compra.'),
                    ])
                    ->action(function (array $data, ?\App\Models\Product $record = null, RelationManager $livewire) {
                        // $livewire->ownerRecord is the Product
                        $product = $livewire->getOwnerRecord();
                        $service = app(\App\Services\InventoryService::class);

                        $quantity = (int) $data['quantity'];
                        $type = $data['type'];
                        $cost = (float) $data['unit_cost'];

                        if (in_array($type, ['purchase', 'return', 'adjustment_inc'])) {
                            $service->addStock($product, $quantity, $cost, $type);
                        } else {
                            $service->removeStock($product, $quantity, $type);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Stock ajustado correctamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
