<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Reviews;
    }

    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Forms\Components\Section::make('Detalles de la Reseña')
                    ->schema([
                        Forms\Components\Group::make([
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->required()
                                ->label('Usuario')
                                ->disabledOn('edit'),
                            Forms\Components\Select::make('product_id')
                                ->relationship('product', 'name')
                                ->searchable()
                                ->required()
                                ->label('Producto')
                                ->disabledOn('edit'),
                        ])->columns(2),

                        Forms\Components\TextInput::make('rating')
                            ->label('Calificación')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(5),
                        Forms\Components\TextInput::make('title')
                            ->label('Título'),
                        Forms\Components\Textarea::make('comment')
                            ->label('Comentario')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_approved')
                            ->label('Aprobado')
                            ->helperText('Activar para mostrar esta reseña en la tienda')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('danger'),

                        Forms\Components\Textarea::make('admin_response')
                            ->label('Respuesta del Administrador')
                            ->rows(3)
                            ->columnSpanFull(),
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
                Tables\Columns\ImageColumn::make('product.thumbnail_url')
                    ->label('Producto')
                    ->circular(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn (string $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color('warning'),
                Tables\Columns\IconColumn::make('is_verified_purchase')
                    ->label('Compra Verificada')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Aprobado')
                    ->boolean()
                    ->action(function ($record, $column) {
                        $name = $column->getName();
                        $record->update([$name => ! $record->$name]);
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('is_approved')
                    ->label('Estado')
                    ->options([
                        true => 'Aprobadas',
                        false => 'Pendientes / Rechazadas',
                    ])
                    ->default(false), // Show pending by default to help admin moderation
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (Review $record) => ! $record->is_approved)
                    ->action(fn (Review $record) => $record->update([
                        'is_approved' => true,
                        'approved_at' => now(),
                    ])),
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->visible(fn (Review $record) => $record->is_approved)
                    ->action(fn (Review $record) => $record->update([
                        'is_approved' => false,
                        'approved_at' => null,
                    ])),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
