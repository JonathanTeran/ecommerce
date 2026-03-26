<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\GlobalIntegrationResource\Pages;
use App\Models\GlobalIntegration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GlobalIntegrationResource extends Resource
{
    protected static ?string $model = GlobalIntegration::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Configuración Global';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Base')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Amigable')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('provider')
                            ->label('Proveedor Interno')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('ej: stripe, paypal, dhl, mercadopago'),
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Integración')
                            ->required()
                            ->options([
                                'payment' => 'Pasarela de Pagos',
                                'logistics' => 'Logística y Envíos',
                                'erp' => 'ERP / Contabilidad',
                                'marketing' => 'Marketing / CRM',
                                'analytics' => 'Analítica',
                                'other' => 'Otro',
                            ]),
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('integrations')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración Operativa')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo Totalmente')
                            ->default(true),
                        Forms\Components\Toggle::make('requires_setup')
                            ->label('Requiere Configuración del Tenant')
                            ->helperText('Si es true, el tenant debe poner sus propias apikeys (ej: Stripe). Si es false, se usan credenciales del SuperAdmin (ej: AWS S3).')
                            ->default(true),
                        Forms\Components\Select::make('supported_countries')
                            ->label('Países Soportados')
                            ->multiple()
                            ->options([
                                'Global' => 'Global',
                                'MX' => 'México',
                                'US' => 'Estados Unidos',
                                'ES' => 'España',
                                'CO' => 'Colombia',
                                'AR' => 'Argentina',
                                'CL' => 'Chile',
                                'PE' => 'Perú',
                            ])
                            ->default(['Global']),
                        Forms\Components\KeyValue::make('global_credentials')
                            ->label('Credenciales Globales (Opcional)')
                            ->helperText('Añade secretos a nivel plataforma (ej: ID de Partner, o API Keys compartidas).')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Integración')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Habilitada')
                    ->boolean(),
                Tables\Columns\IconColumn::make('requires_setup')
                    ->label('Req. Setup')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'payment' => 'Pasarela de Pagos',
                        'logistics' => 'Logística y Envíos',
                        'erp' => 'ERP / Contabilidad',
                        'marketing' => 'Marketing / CRM',
                        'analytics' => 'Analítica',
                        'other' => 'Otro',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Habilitada'),
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
            'index' => Pages\ListGlobalIntegrations::route('/'),
            'create' => Pages\CreateGlobalIntegration::route('/create'),
            'edit' => Pages\EditGlobalIntegration::route('/{record}/edit'),
        ];
    }
}
