<?php

namespace App\Filament\Resources\OrderResource;

use App\Models\Order;
use Filament\Infolists;

class OrderInfolist
{
    /**
     * @return array<int, Infolists\Components\Component>
     */
    public static function schema(): array
    {
        return [
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

            Infolists\Components\Section::make('Facturación Electrónica')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Infolists\Components\Grid::make(2)
                        ->schema([
                            Infolists\Components\TextEntry::make('sri_authorization_status')
                                ->label('Estado SRI')
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => match ($state) {
                                    'authorized' => 'AUTORIZADO',
                                    'pending' => 'EN PROCESO',
                                    'rejected' => 'RECHAZADO',
                                    default => 'SIN ESTADO',
                                })
                                ->color(fn (?string $state): string => match ($state) {
                                    'authorized' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger',
                                    default => 'gray',
                                }),
                            Infolists\Components\TextEntry::make('sri_access_key')
                                ->label('Clave de Acceso')
                                ->copyable()
                                ->placeholder('N/D')
                                ->columnSpanFull(),
                            Infolists\Components\TextEntry::make('sri_authorization_number')
                                ->label('Nº Autorización')
                                ->copyable()
                                ->placeholder('Pendiente'),
                            Infolists\Components\TextEntry::make('sri_authorization_date')
                                ->label('Fecha Autorización')
                                ->dateTime('d/m/Y H:i')
                                ->placeholder('Pendiente'),
                            Infolists\Components\TextEntry::make('sri_error_message')
                                ->label('Detalle del Error')
                                ->color('danger')
                                ->placeholder('Sin errores')
                                ->columnSpanFull()
                                ->visible(fn (Order $record): bool => filled($record->sri_error_message)),
                        ]),
                    Infolists\Components\Actions::make([
                        Infolists\Components\Actions\Action::make('download_xml')
                            ->label('Descargar XML')
                            ->icon('heroicon-o-document-arrow-down')
                            ->color('primary')
                            ->url(fn (Order $record): string => route('admin.orders.sri-xml', $record))
                            ->openUrlInNewTab()
                            ->visible(fn (Order $record): bool => filled($record->sri_authorized_xml_path) || filled($record->sri_xml_path)),
                        Infolists\Components\Actions\Action::make('download_ride')
                            ->label('Descargar RIDE (PDF)')
                            ->icon('heroicon-o-document-text')
                            ->color('success')
                            ->url(fn (Order $record): string => route('admin.orders.ride', $record))
                            ->openUrlInNewTab()
                            ->visible(fn (Order $record): bool => filled($record->sri_access_key)),
                    ])->fullWidth(),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(fn (Order $record): bool => blank($record->sri_access_key))
                ->extraAttributes(fn (Order $record): array => ($record->sri_authorization_status === 'pending' && filled($record->sri_access_key))
                    ? ['wire:poll.10s' => '$refresh']
                    : []),

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

            // Order Timeline
            Infolists\Components\Section::make('Cronología del Pedido')
                ->icon('heroicon-o-clock')
                ->schema([
                    Infolists\Components\ViewEntry::make('statusHistory')
                        ->label('')
                        ->view('filament.infolists.entries.order-timeline'),
                ])
                ->columnSpanFull()
                ->collapsible(),
        ];
    }
}
