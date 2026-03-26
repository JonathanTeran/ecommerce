<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $modelLabel = 'Suscripcion';

    protected static ?string $pluralModelLabel = 'Suscripciones';

    protected static ?string $navigationGroup = 'Gestión SaaS';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Suscripcion')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->relationship('tenant', 'name')
                            ->required()
                            ->searchable()
                            ->label('Tenant'),
                        Forms\Components\Select::make('plan_id')
                            ->relationship('plan', 'name')
                            ->required()
                            ->searchable()
                            ->label('Plan'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Activa',
                                'trialing' => 'Periodo de Prueba',
                                'past_due' => 'Pago Pendiente',
                                'cancelled' => 'Cancelada',
                            ])
                            ->required()
                            ->default('active')
                            ->label('Estado'),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->required()
                            ->default(now())
                            ->label('Inicio'),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Vencimiento'),
                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('Fin Periodo Prueba'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge(),
                Tables\Columns\TextColumn::make('plan.price')
                    ->label('Precio')
                    ->money('USD'),
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
                    ->label('Vencimiento')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Activa',
                        'trialing' => 'Periodo de Prueba',
                        'past_due' => 'Pago Pendiente',
                        'cancelled' => 'Cancelada',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
