<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\StockTransferResource\Pages;
use App\Models\StockTransfer;
use App\Services\InventoryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockTransferResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Inventory;
    }

    protected static ?string $model = StockTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Transferencias';

    protected static ?string $modelLabel = 'Transferencia';

    protected static ?string $pluralModelLabel = 'Transferencias';

    protected static ?int $navigationSort = 5;

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
                Forms\Components\Section::make('Datos de Transferencia')
                    ->schema([
                        Forms\Components\Select::make('from_location_id')
                            ->relationship('fromLocation', 'name')
                            ->label('Ubicación Origen')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->different('to_location_id'),
                        Forms\Components\Select::make('to_location_id')
                            ->relationship('toLocation', 'name')
                            ->label('Ubicación Destino')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->different('from_location_id'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Productos a Transferir')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->label('Producto')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                Forms\Components\TextInput::make('notes')
                                    ->label('Nota')
                                    ->maxLength(255),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar Producto')
                            ->columnSpanFull(),
                    ]),
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
                Tables\Columns\TextColumn::make('transfer_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('fromLocation.name')
                    ->label('Origen')
                    ->sortable(),
                Tables\Columns\TextColumn::make('toLocation.name')
                    ->label('Destino')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Productos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_label')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Borrador' => 'gray',
                        'En Tránsito' => 'warning',
                        'Completada' => 'success',
                        'Cancelada' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('createdByUser.name')
                    ->label('Creada por')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Fecha'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Completada el')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Borrador',
                        'in_transit' => 'En Tránsito',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label('Completar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Completar Transferencia')
                    ->modalDescription('Al completar, se moverá el stock de la ubicación origen a la destino. Esta acción no se puede deshacer.')
                    ->action(function (StockTransfer $record) {
                        app(InventoryService::class)->processTransfer($record);

                        Notification::make()
                            ->title('Transferencia completada')
                            ->body("Se procesaron {$record->items()->count()} productos.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (StockTransfer $record): bool => $record->isDraft()),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (StockTransfer $record) => $record->update(['status' => 'cancelled']))
                    ->visible(fn (StockTransfer $record): bool => $record->isDraft()),
                Tables\Actions\EditAction::make()
                    ->visible(fn (StockTransfer $record): bool => $record->isDraft()),
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
            'index' => Pages\ListStockTransfers::route('/'),
            'create' => Pages\CreateStockTransfer::route('/create'),
            'edit' => Pages\EditStockTransfer::route('/{record}/edit'),
            'view' => Pages\ViewStockTransfer::route('/{record}'),
        ];
    }
}
