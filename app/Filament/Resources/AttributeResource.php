<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\AttributeResource\Pages;
use App\Models\Attribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttributeResource extends Resource
{
    use RequiresModule;

    protected static ?string $model = Attribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $modelLabel = 'Atributo';

    protected static ?string $pluralModelLabel = 'Atributos';

    protected static ?string $navigationGroup = 'Gestión de Tienda';

    protected static ?int $navigationSort = 3;

    protected static function requiredModule(): Module
    {
        return Module::CustomAttributes;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tenant_id')
                ->relationship('tenant', 'name')
                ->label('Tenant')
                ->searchable()
                ->preload()
                ->required()
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                ->columnSpanFull(),
            Forms\Components\Section::make('Información del Atributo')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'string' => 'Texto',
                            'number' => 'Número',
                            'boolean' => 'Sí/No',
                            'select' => 'Lista de Opciones',
                        ])
                        ->required()
                        ->live()
                        ->default('string'),
                    Forms\Components\TagsInput::make('options')
                        ->label('Opciones')
                        ->helperText('Ingrese las opciones disponibles para este atributo.')
                        ->visible(fn (Forms\Get $get) => $get('type') === 'select')
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_filterable')
                        ->label('Filtrable en Tienda')
                        ->helperText('Permitir que los clientes filtren productos por este atributo.'),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Orden')
                        ->numeric()
                        ->default(0),
                ])->columns(2),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'string' => 'Texto',
                        'number' => 'Número',
                        'boolean' => 'Sí/No',
                        'select' => 'Lista',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_filterable')
                    ->label('Filtrable')
                    ->boolean(),
                Tables\Columns\TextColumn::make('values_count')
                    ->label('Productos')
                    ->counts('values')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
