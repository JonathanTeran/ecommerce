<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\Module;
use App\Enums\PlanType;
use App\Filament\SuperAdmin\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Gestión SaaS';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacion del Plan')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Nombre'),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('type')
                            ->options(collect(PlanType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()]))
                            ->required()
                            ->label('Tipo'),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->label('Precio Mensual'),
                        Forms\Components\TextInput::make('max_products')
                            ->numeric()
                            ->nullable()
                            ->label('Max Productos')
                            ->helperText('Dejar vacio para ilimitado'),
                        Forms\Components\TextInput::make('max_users')
                            ->numeric()
                            ->nullable()
                            ->label('Max Usuarios')
                            ->helperText('Dejar vacio para ilimitado'),
                        Forms\Components\CheckboxList::make('modules')
                            ->options(collect(Module::cases())->mapWithKeys(fn ($m) => [$m->value => $m->label()]))
                            ->required()
                            ->label('Modulos Incluidos')
                            ->columns(3),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Orden'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state instanceof PlanType ? $state->label() : (PlanType::tryFrom($state)?->label() ?? $state))
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_products')
                    ->label('Max Productos')
                    ->default('Ilimitado'),
                Tables\Columns\TextColumn::make('max_users')
                    ->label('Max Usuarios')
                    ->default('Ilimitado'),
                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label('Suscripciones'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
