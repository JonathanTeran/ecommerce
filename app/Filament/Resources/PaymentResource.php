<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Enums\PaymentStatus;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Orders;
    }

    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $modelLabel = 'Transacción';

    protected static ?string $pluralModelLabel = 'Transacciones';

    protected static ?string $navigationGroup = 'Gestión de Tienda';

    protected static ?int $navigationSort = 3;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información de la Transacción')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_id')
                            ->label('ID de Transacción')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('order.order_number')
                            ->label('Pedido')
                            ->badge()
                            ->url(fn (Payment $record): ?string => $record->order ? OrderResource::getUrl('edit', ['record' => $record->order]) : null),
                        Infolists\Components\TextEntry::make('gateway')
                            ->label('Pasarela'),
                        Infolists\Components\TextEntry::make('method')
                            ->label('Método'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Estado')
                            ->badge(),
                    ])->columns(3),

                Infolists\Components\Section::make('Montos')
                    ->schema([
                        Infolists\Components\TextEntry::make('amount')
                            ->label('Monto')
                            ->money(fn (Payment $record): string => $record->currency ?? 'USD'),
                        Infolists\Components\TextEntry::make('currency')
                            ->label('Moneda'),
                        Infolists\Components\TextEntry::make('refunded_amount')
                            ->label('Monto Reembolsado')
                            ->money(fn (Payment $record): string => $record->currency ?? 'USD')
                            ->visible(fn (Payment $record): bool => $record->refunded_amount > 0),
                    ])->columns(3),

                Infolists\Components\Section::make('Fechas')
                    ->schema([
                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('Fecha de Pago')
                            ->dateTime()
                            ->placeholder('No pagado'),
                        Infolists\Components\TextEntry::make('refunded_at')
                            ->label('Fecha de Reembolso')
                            ->dateTime()
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime(),
                    ])->columns(3),

                Infolists\Components\Section::make('Respuesta de la Pasarela')
                    ->schema([
                        Infolists\Components\TextEntry::make('gateway_response')
                            ->label('Respuesta')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : ($state ?? '—'))
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('error_message')
                            ->label('Mensaje de Error')
                            ->visible(fn (Payment $record): bool => ! empty($record->error_message))
                            ->color('danger')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('order'))
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Pedido')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Payment $record): ?string => $record->order ? OrderResource::getUrl('edit', ['record' => $record->order]) : null),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('ID Transacción')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('gateway')
                    ->label('Pasarela')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('method')
                    ->label('Método')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money(fn (Payment $record): string => $record->currency ?? 'USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('refunded_amount')
                    ->label('Reembolsado')
                    ->money(fn (Payment $record): string => $record->currency ?? 'USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Fecha de Pago')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(PaymentStatus::class),
                Tables\Filters\SelectFilter::make('gateway')
                    ->label('Pasarela')
                    ->options(fn (): array => Payment::query()->distinct()->whereNotNull('gateway')->pluck('gateway', 'gateway')->toArray()),
            ])
            ->actions([
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
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
