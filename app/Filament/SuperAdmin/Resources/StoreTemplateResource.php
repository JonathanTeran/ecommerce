<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\StoreTemplateResource\Pages;
use App\Models\StoreTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StoreTemplateResource extends Resource
{
    protected static ?string $model = StoreTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $navigationLabel = 'Plantillas de Tienda';

    protected static ?string $modelLabel = 'Plantilla';

    protected static ?string $pluralModelLabel = 'Plantillas';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacion General')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('slug')
                            ->label('Identificador')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripcion')
                            ->rows(3)
                            ->maxLength(500),
                        Forms\Components\Select::make('category')
                            ->label('Categoria')
                            ->options([
                                'fashion' => 'Moda & Ropa',
                                'jewelry' => 'Joyeria & Accesorios',
                                'electronics' => 'Electronica & Tech',
                                'food' => 'Alimentos & Bebidas',
                                'beauty' => 'Belleza & Cuidado',
                                'general' => 'General / Multi-proposito',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Previsualizacion')
                    ->schema([
                        Forms\Components\FileUpload::make('preview_image')
                            ->label('Imagen de Preview (Thumbnail)')
                            ->image()
                            ->disk('public')
                            ->directory('templates/previews')
                            ->visibility('public'),
                        Forms\Components\FileUpload::make('preview_desktop')
                            ->label('Preview Desktop (Completa)')
                            ->image()
                            ->disk('public')
                            ->directory('templates/previews')
                            ->visibility('public'),
                    ])->columns(2),

                Forms\Components\Section::make('Configuracion Tecnica')
                    ->schema([
                        Forms\Components\TextInput::make('assets_path')
                            ->label('Ruta de Assets')
                            ->placeholder('templates/nombre-plantilla')
                            ->helperText('Ruta relativa desde public/'),
                        Forms\Components\TextInput::make('css_file')
                            ->label('Archivo CSS Principal')
                            ->placeholder('templates/nombre/css/style.css'),
                        Forms\Components\KeyValue::make('color_scheme')
                            ->label('Esquema de Colores')
                            ->keyLabel('Variable')
                            ->valueLabel('Color Hex'),
                        Forms\Components\KeyValue::make('fonts')
                            ->label('Tipografias')
                            ->keyLabel('Uso')
                            ->valueLabel('Nombre del Font'),
                        Forms\Components\TagsInput::make('features')
                            ->label('Caracteristicas Incluidas')
                            ->placeholder('Ej: Mega menu, Slider, Blog...'),
                    ])->columns(2),

                Forms\Components\Section::make('Disponibilidad')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                        Forms\Components\Toggle::make('is_premium')
                            ->label('Premium (Requiere plan superior)')
                            ->default(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview_image')
                    ->label('')
                    ->width(80)
                    ->height(50)
                    ->rounded(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->formatStateUsing(fn (StoreTemplate $record): string => $record->category_label)
                    ->color(fn (string $state): string => match ($state) {
                        'fashion' => 'pink',
                        'jewelry' => 'warning',
                        'electronics' => 'info',
                        'food' => 'success',
                        'beauty' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tenants_count')
                    ->label('Tiendas usando')
                    ->counts('tenants')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoria')
                    ->options([
                        'fashion' => 'Moda & Ropa',
                        'jewelry' => 'Joyeria & Accesorios',
                        'electronics' => 'Electronica & Tech',
                        'general' => 'General',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activa'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoreTemplates::route('/'),
            'create' => Pages\CreateStoreTemplate::route('/create'),
            'edit' => Pages\EditStoreTemplate::route('/{record}/edit'),
        ];
    }
}
