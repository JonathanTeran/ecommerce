<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Coupons;
    }

    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Cupón';

    protected static ?string $pluralModelLabel = 'Cupones';

    protected static ?string $navigationGroup = 'Marketing';

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
                Forms\Components\TextInput::make('code')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'fixed' => 'Monto Fijo',
                        'percentage' => 'Porcentaje',
                        'free_shipping' => 'Envío Gratis',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('min_order_amount')
                    ->numeric(),
                Forms\Components\TextInput::make('max_discount_amount')
                    ->numeric(),
                Forms\Components\TextInput::make('usage_limit')
                    ->numeric(),
                Forms\Components\TextInput::make('usage_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('usage_limit_per_user')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('starts_at'),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Select::make('applicable_categories')
                    ->label('Categorías Aplicables')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => \App\Models\Category::pluck('name', 'id'))
                    ->columnSpanFull(),
                Forms\Components\Select::make('applicable_products')
                    ->label('Productos Aplicables')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => \App\Models\Product::pluck('sku', 'id'))
                    ->columnSpanFull(),
                Forms\Components\Select::make('excluded_products')
                    ->label('Productos Excluidos')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => \App\Models\Product::pluck('sku', 'id'))
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('first_order_only')
                    ->required(),
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
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_order_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_discount_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_limit_per_user')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('first_order_only')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
