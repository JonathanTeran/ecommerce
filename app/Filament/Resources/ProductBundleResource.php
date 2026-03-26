<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\ProductBundleResource\Pages;
use App\Models\ProductBundle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductBundleResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Bundles;
    }

    protected static ?string $model = ProductBundle::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $modelLabel = 'Bundle';

    protected static ?string $pluralModelLabel = 'Bundles y Kits';

    protected static ?string $navigationGroup = 'Catálogo';

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

                Forms\Components\Section::make('Informacion del Bundle')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->placeholder('Ej: Kit Completo de Oficina'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripcion')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('price')
                            ->label('Precio del Bundle')
                            ->numeric()
                            ->required()
                            ->prefix('$'),

                        Forms\Components\TextInput::make('compare_price')
                            ->label('Precio de Comparacion')
                            ->numeric()
                            ->prefix('$')
                            ->helperText('Precio sin descuento (para mostrar ahorro)'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Disponible Desde'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Disponible Hasta'),
                    ])->columns(2),

                Forms\Components\Section::make('Productos del Bundle')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->label('Producto')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar Producto')
                            ->itemLabel(fn (array $state): ?string => isset($state['product_id']) ? "Producto #{$state['product_id']}" : null),
                    ]),
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

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('compare_price')
                    ->label('Precio Regular')
                    ->money('USD')
                    ->sortable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Productos')
                    ->counts('items')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Desde')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Hasta')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductBundles::route('/'),
            'create' => Pages\CreateProductBundle::route('/create'),
            'edit' => Pages\EditProductBundle::route('/{record}/edit'),
        ];
    }
}
