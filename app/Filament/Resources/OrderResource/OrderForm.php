<?php

namespace App\Filament\Resources\OrderResource;

use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms;
use Illuminate\Support\Carbon;

class OrderForm
{
    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function schema(): array
    {
        return [
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
                    Forms\Components\Section::make('Información del Pedido')
                        ->schema([
                            Forms\Components\TextInput::make('order_number')
                                ->default('CP-'.random_int(100000, 999999))
                                ->disabled()
                                ->dehydrated()
                                ->required(),
                            Forms\Components\Select::make('user_id')
                                ->label('Cliente')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\Select::make('status')
                                ->label('Estado del Pedido')
                                ->options(\App\Enums\OrderStatus::class)
                                ->required()
                                ->default(\App\Enums\OrderStatus::PENDING),
                            Forms\Components\Select::make('payment_status')
                                ->label('Estado de Pago')
                                ->options(\App\Enums\PaymentStatus::class)
                                ->required()
                                ->default(\App\Enums\PaymentStatus::PENDING),
                            Forms\Components\Select::make('payment_method')
                                ->label('Método de Pago')
                                ->options(\App\Enums\PaymentMethod::class)
                                ->required(),
                            Forms\Components\Toggle::make('is_shipping_different')
                                ->label('Dirección de envío diferente a facturación')
                                ->live(),
                        ])->columns(2),

                    Forms\Components\Section::make('Direcciones')
                        ->schema([
                            Forms\Components\Group::make([
                                Forms\Components\Placeholder::make('shipping_label')
                                    ->label('Dirección de Envío')
                                    ->content(''),
                                Forms\Components\TextInput::make('shipping_address.name')
                                    ->label('Nombre'),
                                Forms\Components\TextInput::make('shipping_address.identity_document')
                                    ->label('Cédula / RUC'),
                                Forms\Components\TextInput::make('shipping_address.email')
                                    ->label('Email')
                                    ->email(),
                                Forms\Components\TextInput::make('shipping_address.phone')
                                    ->label('Teléfono'),
                                Forms\Components\TextInput::make('shipping_address.address')
                                    ->label('Calle principal'),
                                Forms\Components\TextInput::make('shipping_address.city')
                                    ->label('Ciudad'),
                                Forms\Components\TextInput::make('shipping_address.state')
                                    ->label('Provincia / Estado'),
                                Forms\Components\TextInput::make('shipping_address.zip')
                                    ->label('Código Postal'),
                            ]),

                            Forms\Components\Group::make([
                                Forms\Components\Placeholder::make('billing_label')
                                    ->label('Dirección de Facturación')
                                    ->content(''),
                                Forms\Components\TextInput::make('billing_address.name')
                                    ->label('Razón Social / Nombre'),
                                Forms\Components\TextInput::make('billing_address.tax_id')
                                    ->label('RUC / CI'),
                                Forms\Components\TextInput::make('billing_address.phone')
                                    ->label('Teléfono'),
                                Forms\Components\TextInput::make('billing_address.address')
                                    ->label('Dirección Fiscal'),
                                Forms\Components\TextInput::make('billing_address.city')
                                    ->label('Ciudad'),
                                Forms\Components\TextInput::make('billing_address.state')
                                    ->label('Provincia / Estado'),
                                Forms\Components\TextInput::make('billing_address.zip')
                                    ->label('Código Postal'),
                            ]),
                        ])->columns(2),

                    Forms\Components\Section::make('Detalles de Pago')
                        ->schema([
                            Forms\Components\FileUpload::make('payment_proof_path')
                                ->label('Comprobante de Pago')
                                ->image()
                                ->disk('public')
                                ->directory(fn () => 'tenant-'.(app()->bound('current_tenant') ? app('current_tenant')->id : 'shared').'/payment_proofs')
                                ->visibility('public')
                                ->downloadable()
                                ->openable()
                                ->disabled() // Admin shouldn't change this usually
                                ->dehydrated(false)
                                ->columnSpanFull()
                                ->visible(fn ($state) => filled($state)),
                            Forms\Components\TextInput::make('surcharge_amount')
                                ->label('Recargo por Método de Pago')
                                ->numeric()
                                ->prefix('$')
                                ->disabled()
                                ->dehydrated(false)
                                ->visible(fn ($state) => $state > 0),
                        ])->columns(2),

                    Forms\Components\Section::make('Facturación Electrónica')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Placeholder::make('sri_authorization_status')
                                        ->label('Estado SRI')
                                        ->content(fn (?Order $record): string => match ($record?->sri_authorization_status) {
                                            'authorized' => 'AUTORIZADO',
                                            'pending' => 'EN PROCESO',
                                            'rejected' => 'RECHAZADO',
                                            default => 'SIN ESTADO',
                                        }),
                                    Forms\Components\Placeholder::make('sri_access_key')
                                        ->label('Clave de Acceso')
                                        ->content(fn (?Order $record): string => $record?->sri_access_key ?? 'N/D')
                                        ->columnSpanFull(),
                                    Forms\Components\Placeholder::make('sri_authorization_number')
                                        ->label('Nº Autorización')
                                        ->content(fn (?Order $record): string => $record?->sri_authorization_number ?? 'Pendiente'),
                                    Forms\Components\Placeholder::make('sri_authorization_date')
                                        ->label('Fecha Autorización')
                                        ->content(fn (?Order $record): string => $record?->sri_authorization_date
                                            ? Carbon::parse($record->sri_authorization_date)->format('d/m/Y H:i')
                                            : 'Pendiente'
                                        ),
                                    Forms\Components\Placeholder::make('sri_error_message')
                                        ->label('Detalle del Error')
                                        ->content(fn (?Order $record): string => $record?->sri_error_message ?? '')
                                        ->columnSpanFull()
                                        ->visible(fn (?Order $record): bool => filled($record?->sri_error_message)),
                                ]),
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('download_xml')
                                    ->label('Descargar XML')
                                    ->icon('heroicon-o-document-arrow-down')
                                    ->color('primary')
                                    ->url(fn (Order $record): string => route('admin.orders.sri-xml', $record))
                                    ->openUrlInNewTab()
                                    ->visible(fn (Order $record): bool => filled($record->sri_authorized_xml_path) || filled($record->sri_xml_path)),
                                Forms\Components\Actions\Action::make('download_ride')
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
                        ->collapsed(fn (?Order $record): bool => blank($record?->sri_access_key))
                        ->extraAttributes(fn (?Order $record): array => ($record?->sri_authorization_status === 'pending' && filled($record?->sri_access_key))
                            ? ['wire:poll.10s' => '$refresh']
                            : [])
                        ->visible(fn (?Order $record): bool => $record !== null),

                    Forms\Components\Section::make('Ítems del Pedido')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->label('Ítems')
                                ->relationship()
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Producto')
                                        ->relationship('product', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->distinct()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            $set('price', Product::find($state)?->price ?? 0);
                                            $set('variant_id', null); // Reset variant
                                        })
                                        ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => $set('subtotal', $get('price') * $get('quantity')))
                                        ->columnSpan(['md' => 3]),

                                    Forms\Components\Select::make('variant_id')
                                        ->label('Variante')
                                        ->options(fn (Forms\Get $get) => ProductVariant::where('product_id', $get('product_id'))
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(fn ($variant) => [$variant->id => $variant->sku.' - '.$variant->name])
                                        )
                                        ->visible(fn (Forms\Get $get) => ProductVariant::where('product_id', $get('product_id'))->exists())
                                        ->disabled(fn (Forms\Get $get) => ! $get('product_id'))
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('price', ProductVariant::find($state)?->price ?? 0))
                                        ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => $set('subtotal', $get('price') * $get('quantity')))
                                        ->columnSpan(['md' => 3]),

                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Cant.')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => $set('subtotal', $get('price') * $state))
                                        ->columnSpan(['md' => 2]),

                                    Forms\Components\TextInput::make('price')
                                        ->label('P. Unit')
                                        ->numeric()
                                        ->prefix('$')
                                        ->disabled()
                                        ->dehydrated()
                                        ->required()
                                        ->columnSpan(['md' => 2]),

                                    Forms\Components\TextInput::make('subtotal')
                                        ->label('Total')
                                        ->numeric()
                                        ->prefix('$')
                                        ->disabled()
                                        ->dehydrated()
                                        ->required()
                                        ->columnSpan(['md' => 2]),

                                    Forms\Components\KeyValue::make('options')
                                        ->label('Opciones Adicionales')
                                        ->columnSpanFull(),
                                ])
                                ->columns(12)
                                ->defaultItems(1),
                        ]),
                ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Totales')
                        ->schema([
                            Forms\Components\Placeholder::make('subtotal_placeholder')
                                ->label('Subtotal')
                                ->content(fn (Order $record) => '$'.number_format($record->subtotal ?? 0, 2)), // Subtotal is fixed unless items change
                            Forms\Components\TextInput::make('shipping_amount')
                                ->label('Envío')
                                ->numeric()
                                ->prefix('$')
                                ->default(0)
                                ->live(onBlur: true), // Update totals on change
                            Forms\Components\TextInput::make('tax_amount')
                                ->label('Impuesto')
                                ->numeric()
                                ->prefix('$')
                                ->default(0)
                                ->disabled() // Tax is calculated automatically usually
                                ->dehydrated(),
                            Forms\Components\TextInput::make('discount_amount')
                                ->label('Descuento')
                                ->numeric()
                                ->prefix('$')
                                ->default(0)
                                ->live(onBlur: true),
                            Forms\Components\Placeholder::make('total_placeholder')
                                ->label('Total Estimado')
                                ->content(function (Forms\Get $get, ?Order $record) {
                                    $subtotal = $record?->subtotal ?? 0;
                                    $taxRate = GeneralSetting::getTaxRateDecimal();
                                    $taxAmount = $subtotal * $taxRate;

                                    $shipping = (float) $get('shipping_amount');
                                    $discount = (float) $get('discount_amount');

                                    $total = $subtotal - $discount + $taxAmount + $shipping;

                                    return '$'.number_format($total, 2);
                                })
                                ->extraAttributes(['class' => 'text-xl font-bold']),
                        ]),

                    Forms\Components\Section::make('Meta')
                        ->schema([
                            Forms\Components\Textarea::make('notes')
                                ->label('Notas')
                                ->columnSpanFull(),
                            Forms\Components\Placeholder::make('created_at')
                                ->content(fn (Order $record): ?string => $record->created_at?->diffForHumans()),
                            Forms\Components\Placeholder::make('updated_at')
                                ->content(fn (Order $record): ?string => $record->updated_at?->diffForHumans()),
                        ])
                        ->hidden(fn (?Order $record) => $record === null),

                ])->columnSpan(['lg' => 1]),
        ];
    }
}
