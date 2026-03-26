<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Categories;
    }

    protected static ?string $model = Category::class;

    protected static ?string $modelLabel = 'Categoría';

    protected static ?string $pluralModelLabel = 'Categorías';

    protected static ?string $navigationGroup = 'Gestión de Tienda';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->label('Tenant')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->columnSpanFull(),
                Forms\Components\Select::make('parent_id')
                    ->label('Categoría Padre')
                    ->relationship('parent', 'name'),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->formatStateUsing(fn ($state, ?\App\Models\Category $record) => $record?->name ?? $state)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                Forms\Components\TextInput::make('sku_prefix')
                    ->label('Prefijo SKU')
                    ->placeholder('Ej: LAP')
                    ->maxLength(10),
                Forms\Components\TextInput::make('slug')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Forms\Components\Select::make('icon')
                    ->label('Icono')
                    ->searchable()
                    ->options([
                        'heroicon-o-computer-desktop' => 'Computadora / Escritorio',
                        'heroicon-o-device-laptop' => 'Laptop / Portátil',
                        'heroicon-o-device-phone-mobile' => 'Celular / Teléfono',
                        'heroicon-o-device-tablet' => 'Tablet',
                        'heroicon-o-cpu-chip' => 'Procesador / CPU',
                        'heroicon-o-server' => 'Servidor',
                        'heroicon-o-printer' => 'Impresora',
                        'heroicon-o-camera' => 'Cámara',
                        'heroicon-o-speaker-wave' => 'Parlante / Audio',
                        'heroicon-o-wifi' => 'Redes / WiFi',
                        'heroicon-o-bolt' => 'Energía / Cargador',
                        'heroicon-o-battery-50' => 'Batería',
                        'heroicon-o-tv' => 'Minitores / TV',
                        'heroicon-o-rectangle-stack' => 'Componentes',
                        'heroicon-o-game-controller' => 'Gaming',
                        'heroicon-o-headphones' => 'Audífonos',
                        'heroicon-o-micro-sd' => 'Almacenamiento / SD',
                        'heroicon-o-archive-box' => 'Cajas / Gabinetes',
                        'heroicon-o-wrench-screwdriver' => 'Herramientas',
                        'heroicon-o-video-camera' => 'Video Vigilancia',
                    ]),
                Forms\Components\TextInput::make('position')
                    ->label('Posición')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->required(),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Destacado')
                    ->required(),
                Forms\Components\TextInput::make('meta_title')
                    ->label('Título Meta (SEO)'),
                Forms\Components\Textarea::make('meta_description')
                    ->label('Descripción Meta (SEO)')
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Categoría Padre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Posición')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activo'),
                Tables\Columns\ToggleColumn::make('is_featured')
                    ->label('Destacado'),
                Tables\Columns\TextColumn::make('meta_title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Edición')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Fecha Eliminación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
