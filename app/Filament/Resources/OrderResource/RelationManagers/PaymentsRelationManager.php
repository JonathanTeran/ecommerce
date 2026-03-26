<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Pagos';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('ID Transacción')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('gateway')
                    ->label('Pasarela')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('method')
                    ->label('Método'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('refunded_amount')
                    ->label('Reembolsado')
                    ->money('USD')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Fecha de Pago')
                    ->dateTime()
                    ->placeholder('—'),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => PaymentResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
