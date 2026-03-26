<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Enums\PaymentStatus;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\OrderResource\OrderForm;
use App\Filament\Resources\OrderResource\OrderInfolist;
use App\Filament\Resources\OrderResource\OrderTable;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Payment;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Orders;
    }

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    protected static ?string $navigationGroup = 'Gestión de Tienda';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', \App\Enums\OrderStatus::PENDING)->count();
    }

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Facturación Electrónica')
                ->group('Facturación')
                ->icon('heroicon-o-document-text')
                ->url(static::getUrl('sri-invoices'))
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName().'.sri-invoices')),
        ];
    }

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
                                    ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/payment_proofs')
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
                                                ? \Illuminate\Support\Carbon::parse($record->sri_authorization_date)->format('d/m/Y H:i')
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
                                                $set('price', \App\Models\Product::find($state)?->price ?? 0);
                                                $set('variant_id', null); // Reset variant
                                            })
                                            ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => $set('subtotal', $get('price') * $get('quantity')))
                                            ->columnSpan(['md' => 3]),

                                        Forms\Components\Select::make('variant_id')
                                            ->label('Variante')
                                            ->options(fn (Forms\Get $get) => \App\Models\ProductVariant::where('product_id', $get('product_id'))
                                                ->where('is_active', true)
                                                ->get()
                                                ->mapWithKeys(fn ($variant) => [$variant->id => $variant->sku.' - '.$variant->name])
                                            )
                                            ->visible(fn (Forms\Get $get) => \App\Models\ProductVariant::where('product_id', $get('product_id'))->exists())
                                            ->disabled(fn (Forms\Get $get) => ! $get('product_id'))
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('price', \App\Models\ProductVariant::find($state)?->price ?? 0))
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
                                        $taxRate = \App\Models\GeneralSetting::getTaxRateDecimal();
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
            ])->columns(3);
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'tenant']))
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Número de Pedido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge(),
                Tables\Columns\TextColumn::make('sri_authorization_status')
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
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('sri_access_key')
                    ->label('Clave SRI')
                    ->copyable()
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->state(fn (Order $record) => $record->total),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(\App\Enums\OrderStatus::class),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Estado de Pago')
                    ->options(\App\Enums\PaymentStatus::class),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('print_shipping_label')
                        ->label('Imprimir Etiqueta de Envío')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->url(fn (Order $record) => route('order.shipping-label', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Order $record) => $record->shipping_address !== null),
                    Tables\Actions\Action::make('print_invoice')
                        ->label('Imprimir Factura')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->url(fn (Order $record) => route('order.invoice', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('refund')
                        ->label('Reembolsar')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('danger')
                        ->visible(fn (Order $record): bool => $record->payment_status === PaymentStatus::COMPLETED)
                        ->requiresConfirmation()
                        ->modalHeading('Procesar Reembolso')
                        ->form([
                            Forms\Components\Select::make('refund_type')
                                ->label('Tipo de Reembolso')
                                ->options([
                                    'full' => 'Reembolso Total',
                                    'partial' => 'Reembolso Parcial',
                                ])
                                ->required()
                                ->live()
                                ->default('full'),
                            Forms\Components\TextInput::make('refund_amount')
                                ->label('Monto a Reembolsar')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->visible(fn (Forms\Get $get): bool => $get('refund_type') === 'partial')
                                ->maxValue(fn (Order $record): float => (float) $record->total),
                            Forms\Components\Textarea::make('refund_reason')
                                ->label('Razón del Reembolso')
                                ->required()
                                ->rows(2),
                        ])
                        ->action(function (Order $record, array $data): void {
                            $amount = $data['refund_type'] === 'full'
                                ? (float) $record->total
                                : (float) $data['refund_amount'];

                            $isFullRefund = $amount >= (float) $record->total;

                            Payment::create([
                                'tenant_id' => $record->tenant_id,
                                'order_id' => $record->id,
                                'transaction_id' => 'REF-' . strtoupper(substr(md5(uniqid()), 0, 10)),
                                'gateway' => 'manual',
                                'method' => 'refund',
                                'amount' => -$amount,
                                'currency' => $record->currency ?? 'USD',
                                'status' => PaymentStatus::REFUNDED,
                                'refunded_amount' => $amount,
                                'refunded_at' => now(),
                                'paid_at' => now(),
                                'gateway_response' => [
                                    'type' => $data['refund_type'],
                                    'reason' => $data['refund_reason'],
                                    'original_total' => $record->total,
                                ],
                            ]);

                            $record->update([
                                'payment_status' => $isFullRefund
                                    ? PaymentStatus::REFUNDED
                                    : PaymentStatus::PARTIALLY_REFUNDED,
                            ]);

                            if ($isFullRefund) {
                                $record->updateStatus(
                                    \App\Enums\OrderStatus::REFUNDED,
                                    'Reembolso total: $' . number_format($amount, 2) . ' - ' . $data['refund_reason']
                                );
                            }

                            Notification::make()
                                ->title('Reembolso procesado')
                                ->body('$' . number_format($amount, 2) . ' reembolsado exitosamente')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('sri_authorize')
                        ->label('SRI Autorizar')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->color('warning')
                        ->action(function (Order $record, \App\Services\SriService $sriService) {
                            try {
                                // 1. Generate Access Key
                                $accessKey = $sriService->generateAccessKey($record);
                                $record->update(['sri_access_key' => $accessKey]);

                                // 2. Generate XML
                                $xml = $sriService->generateInvoiceXml($record, $accessKey);

                                // 3. Sign XML
                                try {
                                    $xmlSigned = $sriService->signXml($xml);
                                } catch (\Exception $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Error Firma')
                                        ->body('No se pudo firmar el XML: '.$e->getMessage())
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                // 4. Save XML
                                $path = 'xml/facturas/'.$accessKey.'.xml';
                                \Illuminate\Support\Facades\Storage::put($path, $xmlSigned);
                                $record->update(['sri_xml_path' => $path]);

                                // 5. Send to SRI (Recepcion)
                                $response = $sriService->sendToRecepcion($xmlSigned);

                                if (! $sriService->shouldAttemptAuthorization($response)) {
                                    $errorMessage = $response['message'] ?? 'Sin mensaje';
                                    $errors = $response['errors'] ?? null;
                                    if (is_array($errors) && $errors !== []) {
                                        $errorMessage .= ' | '.implode(' | ', array_slice($errors, 0, 3));
                                    }

                                    $source = $response['source'] ?? null;
                                    $validation = $response['validation'] ?? null;
                                    $sourceLabel = match ($source) {
                                        'offline' => 'VALIDACION OFFLINE',
                                        'system' => 'SISTEMA',
                                        default => 'SRI',
                                    };

                                    if ($source === 'offline' && is_string($validation) && $validation !== '') {
                                        $sourceLabel .= ' ('.$validation.')';
                                    }

                                    $errorMessage = $sourceLabel.': '.$errorMessage;

                                    \Filament\Notifications\Notification::make()
                                        ->title('SRI Rechazado')
                                        ->body('Estado: '.$response['status'].' - '.$errorMessage)
                                        ->danger()
                                        ->send();
                                    $record->update([
                                        'sri_authorization_status' => 'rejected',
                                        'sri_error_message' => $errorMessage,
                                    ]);

                                    return;
                                }

                                // 6. Check Authorization (Wait a bit or run immediately?)
                                sleep(2); // Simple delay
                                $authResponse = $sriService->authorize($accessKey);

                                if ($authResponse['status'] === 'AUTORIZADO') {
                                    $update = [
                                        'sri_authorization_status' => 'authorized',
                                        'sri_authorization_date' => $authResponse['date'] ?? now(),
                                        'sri_authorization_number' => $authResponse['authorization_number'],
                                        'sri_error_message' => null,
                                    ];

                                    if (! empty($authResponse['xml'])) {
                                        $update['sri_authorized_xml_path'] = $sriService->storeAuthorizedXml($record, $authResponse['xml']);
                                    }

                                    $record->update($update);

                                    \Filament\Notifications\Notification::make()
                                        ->title('SRI Autorizado')
                                        ->body('Número: '.$authResponse['authorization_number'])
                                        ->success()
                                        ->send();
                                } elseif ($authResponse['status'] === 'NO AUTORIZADO') {
                                    $message = ($authResponse['source'] ?? null) === 'system'
                                        ? 'SISTEMA: '.$authResponse['message']
                                        : 'SRI: '.$authResponse['message'];

                                    $record->update([
                                        'sri_authorization_status' => 'rejected',
                                        'sri_error_message' => $message,
                                    ]);
                                    \Filament\Notifications\Notification::make()
                                        ->title('SRI Rechazado')
                                        ->body($message)
                                        ->danger()
                                        ->send();
                                } else {
                                    $message = ($authResponse['source'] ?? null) === 'system'
                                        ? 'SISTEMA: '.$authResponse['message']
                                        : 'SRI: '.$authResponse['message'];

                                    $record->update([
                                        'sri_authorization_status' => 'pending',
                                        'sri_error_message' => $message,
                                    ]);
                                    $sriService->scheduleAuthorizationCheck($record);
                                    \Filament\Notifications\Notification::make()
                                        ->title('SRI Pendiente')
                                        ->body('Enviado pero no autorizado: '.$message)
                                        ->warning()
                                        ->send();
                                }

                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error Sistema')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (Order $record) => $record->sri_authorization_status !== 'authorized'),
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
            RelationManagers\PaymentsRelationManager::class,
            RelationManagers\ReturnsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'sri-invoices' => Pages\ListSriInvoices::route('/facturacion-electronica'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
