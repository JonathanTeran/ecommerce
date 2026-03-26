<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Products;
    }

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

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
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Detalles del Producto')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->unique(Product::class, 'slug', ignoreRecord: true),
                                Forms\Components\MarkdownEditor::make('description')
                                    ->label('Descripción')
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('short_description')
                                    ->label('Descripción Corta')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Imágenes')
                            ->schema([
                                Forms\Components\SpatieMediaLibraryFileUpload::make('product_images')
                                    ->label('Imágenes')
                                    ->collection('images')
                                    ->multiple()
                                    ->reorderable()
                                    ->responsiveImages()
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Variantes')
                            ->schema([
                                Forms\Components\Repeater::make('variants')
                                    ->label('Variantes')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required(),
                                        Forms\Components\TextInput::make('sku')
                                            ->label('SKU')
                                            ->required(),
                                        Forms\Components\TextInput::make('price')
                                            ->label('Precio')
                                            ->numeric()
                                            ->prefix('$')
                                            ->required(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),
                                        Forms\Components\KeyValue::make('options')
                                            ->label('Opciones')
                                            ->keyLabel('Opción')
                                            ->valueLabel('Valor'),
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Activo')
                                            ->default(true),
                                    ])
                                    ->columns(2)
                                    ->collapsed(),
                            ]),

                        Forms\Components\Section::make('Atributos Personalizados')
                            ->description('Valores de atributos personalizados para este producto.')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema(function () {
                                $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

                                if (! $tenant) {
                                    return [];
                                }

                                $attributes = \App\Models\Attribute::withoutGlobalScopes()
                                    ->where('tenant_id', $tenant->id)
                                    ->orderBy('sort_order')
                                    ->get();

                                return $attributes->map(function ($attribute) {
                                    $fieldName = "custom_attributes.{$attribute->id}";

                                    return match ($attribute->type) {
                                        'number' => Forms\Components\TextInput::make($fieldName)
                                            ->label($attribute->name)
                                            ->numeric(),
                                        'boolean' => Forms\Components\Toggle::make($fieldName)
                                            ->label($attribute->name),
                                        'select' => Forms\Components\Select::make($fieldName)
                                            ->label($attribute->name)
                                            ->options(array_combine($attribute->options ?? [], $attribute->options ?? [])),
                                        default => Forms\Components\TextInput::make($fieldName)
                                            ->label($attribute->name),
                                    };
                                })->toArray();
                            })
                            ->collapsible()
                            ->collapsed()
                            ->columnSpanFull()
                            ->visible(function () {
                                $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

                                if (! $tenant) {
                                    return false;
                                }

                                return $tenant->hasModule(Module::CustomAttributes)
                                    && \App\Models\Attribute::withoutGlobalScopes()
                                        ->where('tenant_id', $tenant->id)
                                        ->exists();
                            }),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Estado')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Destacado'),
                                Forms\Components\Toggle::make('is_new')
                                    ->label('Nuevo'),
                            ]),

                        Forms\Components\Section::make('Asociaciones')
                            ->schema([
                                Forms\Components\Select::make('brand_id')
                                    ->label('Marca')
                                    ->relationship('brand', 'name')
                                    ->preload()
                                    ->searchable(),
                                Forms\Components\Select::make('category_id')
                                    ->label('Categoría')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable(),
                            ]),

                        Forms\Components\Section::make('Precios')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Precio')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required(),
                                Forms\Components\TextInput::make('compare_price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->label('Precio Original'),
                                Forms\Components\TextInput::make('cost')
                                    ->numeric()
                                    ->prefix('$')
                                    ->label('Costo por Item'),
                            ]),

                        Forms\Components\Section::make('Inventario')
                            ->schema([
                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU')
                                    ->helperText('Dejar vacío para generar automáticamente basado en la categoría')
                                    ->unique(Product::class, 'sku', ignoreRecord: true),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                Forms\Components\TextInput::make('low_stock_threshold')
                                    ->label('Umbral de Stock Bajo')
                                    ->numeric()
                                    ->default(5)
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Especificaciones')
                            ->schema([
                                Forms\Components\KeyValue::make('specifications')
                                    ->label('Especificaciones')
                                    ->keyLabel('Propiedad')
                                    ->valueLabel('Valor'),
                            ]),

                        Forms\Components\Section::make('Productos Relacionados')
                            ->schema([
                                Forms\Components\Select::make('relatedProducts')
                                    ->label('Productos Relacionados')
                                    ->relationship('relatedProducts', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Selecciona productos para mostrar como relacionados'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['brand', 'category', 'tenant']))
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                Tables\Columns\SpatieMediaLibraryImageColumn::make('product_image')
                    ->collection('images')
                    ->label('Imagen'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Marca')
                    ->sortable()
                    ->searchable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable()
                    ->label('Existencias')
                    ->color(fn (Product $record) => $record->quantity <= $record->low_stock_threshold ? 'danger' : 'success'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->label('Marca')
                    ->relationship('brand', 'name'),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Solo Activos'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('adjust_stock')
                        ->label('Ajustar Stock')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('adjustment')
                                ->label('Cantidad a Ajustar')
                                ->helperText('Ej: 5 para sumar, -5 para restar')
                                ->numeric()
                                ->required(),
                            Forms\Components\Textarea::make('reason')
                                ->label('Razón del ajuste'),
                        ])
                        ->action(function (Product $record, array $data) {
                            $record->increment('quantity', (int) $data['adjustment']);
                        }),
                    Tables\Actions\Action::make('duplicate')
                        ->label('Duplicar')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Duplicar Producto')
                        ->modalDescription(fn (Product $record): string => "Se creará una copia de \"{$record->name}\" con stock en 0 y estado inactivo.")
                        ->action(function (Product $record): void {
                            $newProduct = $record->replicate([
                                'slug',
                                'sku',
                                'views_count',
                                'sales_count',
                            ]);
                            $newProduct->name = $record->name . ' (Copia)';
                            $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(4);
                            $newProduct->sku = null;
                            $newProduct->quantity = 0;
                            $newProduct->is_active = false;
                            $newProduct->is_featured = false;
                            $newProduct->views_count = 0;
                            $newProduct->sales_count = 0;
                            $newProduct->save();

                            Notification::make()
                                ->title('Producto duplicado')
                                ->body("\"{$newProduct->name}\" creado exitosamente")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('print_barcode')
                        ->label('Imprimir Código')
                        ->icon('heroicon-o-qr-code')
                        ->color('info')
                        ->url(fn (Product $record) => route('product.barcode', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ProductResource\RelationManagers\InventoryMovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
