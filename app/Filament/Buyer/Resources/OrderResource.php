<?php

namespace App\Filament\Buyer\Resources;

use App\Filament\Buyer\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Mis Pedidos';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('order_number')
                    ->label('Pedido #')
                    ->content(fn (Order $record): string => $record->order_number),
                Forms\Components\Placeholder::make('status')
                    ->label('Estado')
                    ->content(fn (Order $record): string => $record->status->getLabel()),
                Forms\Components\Placeholder::make('total')
                    ->content(fn (Order $record): string => $record->formatted_total),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header Section - Invoice Style
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('order_number')
                                        ->label('Número de Pedido')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->weight('bold')
                                        ->color('primary'),
                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('Fecha del Pedido')
                                        ->dateTime('d/m/Y H:i'),
                                ]),
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('status')
                                        ->label('Estado del Pedido')
                                        ->badge()
                                        ->formatStateUsing(fn ($state) => $state->getLabel())
                                        ->color(fn ($state) => match ($state->value) {
                                            'pending' => 'warning',
                                            'confirmed', 'processing' => 'info',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'gray',
                                        }),
                                    Infolists\Components\TextEntry::make('payment_status')
                                        ->label('Estado de Pago')
                                        ->badge()
                                        ->formatStateUsing(fn ($state) => $state->getLabel())
                                        ->color(fn ($state) => match ($state->value) {
                                            'pending' => 'warning',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'refunded' => 'info',
                                            default => 'gray',
                                        }),
                                ])->columnStart(2),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Address Details Section
                Infolists\Components\Section::make('Direcciones')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                // Shipping Address
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('shipping_address_label')
                                        ->label('Dirección de Envío')
                                        ->weight('bold')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->color('primary'),
                                    Infolists\Components\TextEntry::make('shipping_address.name')
                                        ->label('Nombre Completo')
                                        ->icon('heroicon-o-user'),
                                    Infolists\Components\TextEntry::make('shipping_address.identity_document')
                                        ->label('Cédula / RUC')
                                        ->icon('heroicon-o-identification'),
                                    Infolists\Components\TextEntry::make('shipping_address.email')
                                        ->label('Correo Electrónico')
                                        ->icon('heroicon-o-envelope'),
                                    Infolists\Components\TextEntry::make('shipping_address.phone')
                                        ->label('Teléfono')
                                        ->icon('heroicon-o-phone'),
                                    Infolists\Components\TextEntry::make('shipping_address.full_address')
                                        ->label('Dirección Completa')
                                        ->icon('heroicon-o-map')
                                        ->getStateUsing(fn ($record) => ($record->shipping_address['address'] ?? '').', '.
                                            ($record->shipping_address['city'] ?? '').', '.
                                            ($record->shipping_address['state'] ?? '')
                                        ),
                                ]),

                                // Billing Address
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('billing_address_label')
                                        ->label('Datos de Facturación')
                                        ->weight('bold')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->color('secondary'),
                                    Infolists\Components\TextEntry::make('billing_address.name')
                                        ->label('Razón Social / Nombre')
                                        ->icon('heroicon-o-building-office'),
                                    Infolists\Components\TextEntry::make('billing_address.tax_id')
                                        ->label('RUC / CI')
                                        ->weight('bold')
                                        ->icon('heroicon-o-identification'),
                                    Infolists\Components\TextEntry::make('billing_address.phone')
                                        ->label('Teléfono')
                                        ->icon('heroicon-o-phone'),
                                    Infolists\Components\TextEntry::make('billing_address.full_address')
                                        ->label('Dirección Fiscal')
                                        ->icon('heroicon-o-map')
                                        ->getStateUsing(fn ($record) => ($record->billing_address['address'] ?? '').', '.
                                           ($record->billing_address['city'] ?? '').', '.
                                           ($record->billing_address['state'] ?? '')
                                        ),
                                ]),
                            ]),
                    ])
                    ->collapsible(),

                // Payment Method Section
                Infolists\Components\Section::make('Método de Pago')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Método'),
                        Infolists\Components\ImageEntry::make('payment_proof_path')
                            ->label('Comprobante de Pago')
                            ->disk('public')
                            ->height(200)
                            ->visible(fn ($record) => $record->payment_proof_path),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Order Items Section - Invoice Style
                Infolists\Components\Section::make('Productos del Pedido')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\ViewEntry::make('product.thumbnail_url')
                                    ->label('')
                                    ->view('filament.buyer.orders.infolist.item-image'),
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('name')
                                        ->label('')
                                        ->weight('bold')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Medium),
                                    Infolists\Components\TextEntry::make('sku')
                                        ->label('')
                                        ->prefix('SKU: ')
                                        ->color('gray')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Small),
                                ])->columnSpan(2),
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
                            ->columns(6)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // Totals Section - Invoice Style
                Infolists\Components\Section::make('Resumen del Pedido')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\Group::make([
                                    // Empty space or notes
                                    Infolists\Components\TextEntry::make('notes')
                                        ->label('Notas del Pedido')
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

                                            Infolists\Components\TextEntry::make('tax_label')
                                                ->label('')
                                                ->default('IVA (15%)')
                                                ->alignEnd(),
                                            Infolists\Components\TextEntry::make('tax_amount')
                                                ->label('')
                                                ->money('USD')
                                                ->alignEnd(),

                                            Infolists\Components\TextEntry::make('surcharge_label')
                                                ->label('')
                                                ->default('Recargo')
                                                ->alignEnd()
                                                ->visible(fn ($record) => $record->surcharge_amount > 0),
                                            Infolists\Components\TextEntry::make('surcharge_amount')
                                                ->label('')
                                                ->money('USD')
                                                ->alignEnd()
                                                ->visible(fn ($record) => $record->surcharge_amount > 0),

                                            Infolists\Components\TextEntry::make('shipping_label')
                                                ->label('')
                                                ->default('Envío')
                                                ->alignEnd(),
                                            Infolists\Components\TextEntry::make('shipping_display')
                                                ->label('')
                                                ->default('Gratis')
                                                ->color('success')
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

                // Tracking Section (if shipped)
                Infolists\Components\Section::make('Información de Envío')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Infolists\Components\TextEntry::make('tracking_number')
                            ->label('Número de Seguimiento')
                            ->copyable()
                            ->placeholder('Pendiente'),
                        Infolists\Components\TextEntry::make('shipped_at')
                            ->label('Fecha de Envío')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Pendiente'),
                        Infolists\Components\TextEntry::make('delivered_at')
                            ->label('Fecha de Entrega')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Pendiente'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(fn ($record) => ! $record->tracking_number),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Pedido #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\ViewColumn::make('items_list')
                    ->label('Productos')
                    ->view('filament.buyer.orders.columns.items-list')
                    ->state(fn (Order $record) => $record->items),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => match ($state->value) {
                        'pending' => 'warning',
                        'confirmed', 'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => match ($state->value) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
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
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'processing' => 'Procesando',
                        'shipped' => 'Enviado',
                        'delivered' => 'Entregado',
                        'cancelled' => 'Cancelado',
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
            'index' => Pages\ManageOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
