<?php

namespace App\Filament\Buyer\Resources;

use App\Filament\Buyer\Resources\QuotationResource\Pages;
use App\Models\Quotation;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Mis Cotizaciones';

    protected static ?string $modelLabel = 'Cotización';

    protected static ?string $pluralModelLabel = 'Cotizaciones';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('quotation_number')
                                        ->label('Número de Cotización')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->weight('bold')
                                        ->color('primary'),
                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('Fecha de Solicitud')
                                        ->dateTime('d/m/Y H:i'),
                                ]),
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('status')
                                        ->label('Estado')
                                        ->badge(),
                                    Infolists\Components\TextEntry::make('valid_until')
                                        ->label('Válida hasta')
                                        ->date('d/m/Y')
                                        ->placeholder('Sin fecha límite'),
                                ])->columnStart(2),
                            ]),
                    ])
                    ->columnSpanFull(),

                Infolists\Components\Section::make('Productos Cotizados')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Producto')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Cantidad')
                                    ->alignCenter(),
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Precio Unit.')
                                    ->money('USD')
                                    ->alignEnd(),
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('USD')
                                    ->weight('bold')
                                    ->alignEnd(),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Infolists\Components\Section::make('Resumen')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('customer_notes')
                                        ->label('Notas')
                                        ->default('Sin notas')
                                        ->columnSpanFull(),
                                ]),
                                Infolists\Components\Group::make([
                                    Infolists\Components\Grid::make(2)
                                        ->schema([
                                            Infolists\Components\TextEntry::make('subtotal_label')
                                                ->label('')
                                                ->default('Subtotal')
                                                ->alignEnd(),
                                            Infolists\Components\TextEntry::make('subtotal')
                                                ->label('')
                                                ->money('USD')
                                                ->alignEnd(),
                                            Infolists\Components\TextEntry::make('discount_label')
                                                ->label('')
                                                ->default('Descuento')
                                                ->alignEnd()
                                                ->visible(fn ($record): bool => $record->discount_amount > 0),
                                            Infolists\Components\TextEntry::make('discount_amount')
                                                ->label('')
                                                ->money('USD')
                                                ->alignEnd()
                                                ->visible(fn ($record): bool => $record->discount_amount > 0),
                                            Infolists\Components\TextEntry::make('tax_label')
                                                ->label('')
                                                ->default('IVA')
                                                ->alignEnd(),
                                            Infolists\Components\TextEntry::make('tax_amount')
                                                ->label('')
                                                ->money('USD')
                                                ->alignEnd(),
                                            Infolists\Components\TextEntry::make('total_label')
                                                ->label('')
                                                ->default('TOTAL')
                                                ->weight('bold')
                                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                ->alignEnd(),
                                            Infolists\Components\TextEntry::make('total')
                                                ->label('')
                                                ->money('USD')
                                                ->weight('bold')
                                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                ->color('primary')
                                                ->alignEnd(),
                                        ]),
                                ])->columnStart(2),
                            ]),
                    ])
                    ->columnSpanFull(),

                Infolists\Components\Section::make('Motivo de Rechazo')
                    ->icon('heroicon-o-x-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('')
                            ->color('danger'),
                    ])
                    ->visible(fn ($record): bool => $record->rejection_reason !== null)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quotation_number')
                    ->label('Cotización #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Válida hasta')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                        'expired' => 'Expirada',
                        'converted' => 'Convertida a Pedido',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalle'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageQuotations::route('/'),
            'view' => Pages\ViewQuotation::route('/{record}'),
        ];
    }
}
