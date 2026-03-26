<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Resources\ProductReturnResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReturnsRelationManager extends RelationManager
{
    protected static string $relationship = 'returns';

    protected static ?string $title = 'Devoluciones';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('return_number')
                    ->label('Número')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Razón')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('refund_amount')
                    ->label('Monto Reembolso')
                    ->money('USD')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => ProductReturnResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
