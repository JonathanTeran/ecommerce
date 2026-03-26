<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\FeatureFlagResource\Pages;
use App\Models\FeatureFlag;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeatureFlagResource extends Resource
{
    protected static ?string $model = FeatureFlag::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Configuración Global';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración General')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la Feature')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('key')
                            ->label('Key Interna')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Ej: beta-checkout, advanced-reports'),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Disponibilidad')
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled_globally')
                            ->label('Habilitado Globalmente')
                            ->helperText('Si se activa, todos los tenants tendrán esta feature a menos que estén en la lista de excluidos.')
                            ->default(false)
                            ->live(),
                            
                        Forms\Components\Select::make('enabled_tenants')
                            ->label('Habilitar solo para estos Tenants')
                            ->multiple()
                            ->options(Tenant::pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => ! $get('is_enabled_globally'))
                            ->helperText('Solo estos tenants específicos tendrán acceso.'),
                            
                        Forms\Components\Select::make('disabled_tenants')
                            ->label('Excluir para estos Tenants')
                            ->multiple()
                            ->options(Tenant::pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => $get('is_enabled_globally'))
                            ->helperText('Todos tendrán acceso, EXCEPTO los seleccionados aquí.'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Feature')
                    ->searchable(),
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_enabled_globally')
                    ->label('Global')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled_globally')
                    ->label('Estado Global'),
            ])
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
            'index' => Pages\ListFeatureFlags::route('/'),
            'create' => Pages\CreateFeatureFlag::route('/create'),
            'edit' => Pages\EditFeatureFlag::route('/{record}/edit'),
        ];
    }
}
