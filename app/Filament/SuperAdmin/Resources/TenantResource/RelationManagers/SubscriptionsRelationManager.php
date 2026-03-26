<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\RelationManagers;

use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $title = 'Suscripciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plan_id')
                    ->label('Plan')
                    ->options(Plan::where('is_active', true)->pluck('name', 'id'))
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activa',
                        'trialing' => 'Periodo de prueba',
                        'past_due' => 'Vencida',
                        'cancelled' => 'Cancelada',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Inicio')
                    ->required()
                    ->default(now()),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('Fin'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'trialing' => 'info',
                        'past_due' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin')
                    ->dateTime(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
