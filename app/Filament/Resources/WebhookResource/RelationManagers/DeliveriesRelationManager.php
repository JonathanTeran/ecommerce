<?php

namespace App\Filament\Resources\WebhookResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveriesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveries';

    protected static ?string $title = 'Entregas';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('response_status_code')
                    ->label('Estado HTTP')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 400 && $state < 500 => 'warning',
                        $state >= 500 => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('attempts')
                    ->label('Intentos')
                    ->numeric(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Entregado')
                    ->dateTime()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('failed_at')
                    ->label('Fallido')
                    ->dateTime()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Payload')
                    ->modalHeading('Detalle de Entrega')
                    ->form([
                        \Filament\Forms\Components\KeyValue::make('payload')
                            ->label('Payload'),
                        \Filament\Forms\Components\Textarea::make('response_body')
                            ->label('Respuesta')
                            ->rows(6)
                            ->disabled(),
                    ]),
            ]);
    }
}
