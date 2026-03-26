<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $modelLabel = 'Registro de Actividad';

    protected static ?string $pluralModelLabel = 'Registro de Actividades';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 99;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Tipo')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripcion')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID'),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->searchable()
                    ->default('Sistema'),

                Tables\Columns\TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Tipo')
                    ->options(fn () => Activity::query()->distinct()->pluck('log_name', 'log_name')->toArray()),
                Tables\Filters\SelectFilter::make('event')
                    ->label('Evento')
                    ->options([
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn (Activity $record) => view('filament.resources.activity-log.view', ['record' => $record])),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
