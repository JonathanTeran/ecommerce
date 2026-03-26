<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\ProductReturnResource\Pages;
use App\Models\ProductReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductReturnResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Returns;
    }

    protected static ?string $model = ProductReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $modelLabel = 'Devolucion';

    protected static ?string $pluralModelLabel = 'Devoluciones';

    protected static ?string $navigationGroup = 'Gestión de Tienda';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', ReturnStatus::Requested)->count() ?: null;
    }

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

                Forms\Components\Section::make('Informacion de la Devolucion')
                    ->schema([
                        Forms\Components\TextInput::make('return_number')
                            ->label('Numero de Devolucion')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),

                        Forms\Components\Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->label('Pedido')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Cliente')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\Select::make('reason')
                            ->label('Motivo')
                            ->options(collect(ReturnReason::cases())->mapWithKeys(fn ($r) => [$r->value => $r->label()]))
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options(collect(ReturnStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->required()
                            ->default('requested'),

                        Forms\Components\Select::make('resolution_type')
                            ->label('Tipo de Resolucion')
                            ->options([
                                'refund' => 'Reembolso',
                                'exchange' => 'Cambio de Producto',
                                'store_credit' => 'Credito en Tienda',
                            ]),

                        Forms\Components\TextInput::make('refund_amount')
                            ->label('Monto de Reembolso')
                            ->numeric()
                            ->prefix('$'),
                    ])->columns(2),

                Forms\Components\Section::make('Detalles')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripcion del Cliente')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notas del Administrador')
                            ->rows(3)
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

                Tables\Columns\TextColumn::make('return_number')
                    ->label('# Devolucion')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('# Pedido')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->formatStateUsing(fn (ReturnReason $state) => $state->label()),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (ReturnStatus $state) => $state->label())
                    ->color(fn (ReturnStatus $state) => $state->color()),

                Tables\Columns\TextColumn::make('refund_amount')
                    ->label('Reembolso')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(collect(ReturnStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                Tables\Filters\SelectFilter::make('reason')
                    ->label('Motivo')
                    ->options(collect(ReturnReason::cases())->mapWithKeys(fn ($r) => [$r->value => $r->label()])),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (ProductReturn $record) => $record->status === ReturnStatus::Requested)
                    ->requiresConfirmation()
                    ->action(fn (ProductReturn $record) => $record->updateStatus(ReturnStatus::Approved)),
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->visible(fn (ProductReturn $record) => $record->status === ReturnStatus::Requested)
                    ->requiresConfirmation()
                    ->action(fn (ProductReturn $record) => $record->updateStatus(ReturnStatus::Rejected)),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReturns::route('/'),
            'create' => Pages\CreateProductReturn::route('/create'),
            'edit' => Pages\EditProductReturn::route('/{record}/edit'),
        ];
    }
}
