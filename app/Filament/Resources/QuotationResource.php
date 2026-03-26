<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Enums\QuotationStatus;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Quotation;
use App\Services\QuotationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuotationResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Quotations;
    }

    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Gestión de Tienda';

    protected static ?string $modelLabel = 'Cotizacion';

    protected static ?string $pluralModelLabel = 'Cotizaciones';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', QuotationStatus::Pending)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del Cliente')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Cliente (Usuario)')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                            if ($state) {
                                $user = \App\Models\User::find($state);
                                if ($user) {
                                    $set('customer_name', $user->name);
                                    $set('customer_email', $user->email);
                                }
                            }
                        }),
                    Forms\Components\TextInput::make('customer_name')
                        ->label('Nombre del Cliente')
                        ->required(),
                    Forms\Components\TextInput::make('customer_email')
                        ->label('Email del Cliente')
                        ->email()
                        ->required(),
                    Forms\Components\TextInput::make('customer_phone')
                        ->label('Telefono')
                        ->tel(),
                    Forms\Components\TextInput::make('customer_company')
                        ->label('Empresa'),
                ])->columns(2),

            Forms\Components\Section::make('Productos')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->label('')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Producto')
                                ->relationship('product', 'sku')
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                    if ($state) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('name', $product->getTranslation('name', 'es'));
                                            $set('sku', $product->sku);
                                            $set('price', $product->price);
                                            $set('quantity', 1);
                                            $set('subtotal', $product->price);
                                        }
                                    }
                                })
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre')
                                ->required(),
                            Forms\Components\TextInput::make('sku')
                                ->label('SKU'),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Cant.')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => $set('subtotal', round(($get('price') ?? 0) * ($get('quantity') ?? 1), 2))),
                            Forms\Components\TextInput::make('price')
                                ->label('P. Unit.')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => $set('subtotal', round(($get('price') ?? 0) * ($get('quantity') ?? 1), 2))),
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->prefix('$')
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columns(7)
                        ->defaultItems(1)
                        ->reorderable(false)
                        ->addActionLabel('Agregar producto'),
                ]),

            Forms\Components\Section::make('Totales y Configuracion')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options(QuotationStatus::class)
                        ->default(QuotationStatus::Pending)
                        ->required(),
                    Forms\Components\DatePicker::make('valid_until')
                        ->label('Valida hasta')
                        ->default(now()->addDays(15)),
                    Forms\Components\TextInput::make('discount_amount')
                        ->label('Descuento')
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    Forms\Components\Textarea::make('customer_notes')
                        ->label('Notas del Cliente')
                        ->rows(2),
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Notas del Admin')
                        ->rows(2),
                ])->columns(3),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informacion de la Cotizacion')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('quotation_number')
                                    ->label('Numero'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha')
                                    ->dateTime(),
                            ]),
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('valid_until')
                                    ->label('Valida hasta')
                                    ->date(),
                                Infolists\Components\TextEntry::make('placed_at')
                                    ->label('Enviada')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('convertedOrder.order_number')
                                    ->label('Pedido Generado')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Datos del Cliente')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('customer_name')
                                    ->label('Nombre'),
                                Infolists\Components\TextEntry::make('customer_email')
                                    ->label('Email'),
                                Infolists\Components\TextEntry::make('customer_phone')
                                    ->label('Telefono')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('customer_company')
                                    ->label('Empresa')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Productos')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Producto'),
                                Infolists\Components\TextEntry::make('sku')
                                    ->label('SKU'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Cant.'),
                                Infolists\Components\TextEntry::make('price')
                                    ->label('P. Unit.')
                                    ->money('USD'),
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('USD'),
                            ])
                            ->columns(5),
                    ]),

                Infolists\Components\Section::make('Totales')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('USD'),
                                Infolists\Components\TextEntry::make('discount_amount')
                                    ->label('Descuento')
                                    ->money('USD'),
                                Infolists\Components\TextEntry::make('tax_amount')
                                    ->label('IVA')
                                    ->money('USD'),
                                Infolists\Components\TextEntry::make('total')
                                    ->label('Total')
                                    ->money('USD')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                            ]),
                    ]),

                Infolists\Components\Section::make('Notas')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer_notes')
                            ->label('Notas del Cliente')
                            ->placeholder('Sin notas'),
                        Infolists\Components\TextEntry::make('admin_notes')
                            ->label('Notas del Admin')
                            ->placeholder('Sin notas'),
                    ])
                    ->columns(1),

                Infolists\Components\Section::make('Direcciones')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\KeyValueEntry::make('shipping_address')
                                    ->label('Direccion de Envio'),
                                Infolists\Components\KeyValueEntry::make('billing_address')
                                    ->label('Direccion de Facturacion'),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Rechazo')
                    ->schema([
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Razon de Rechazo'),
                        Infolists\Components\TextEntry::make('rejectedByUser.name')
                            ->label('Rechazada por'),
                        Infolists\Components\TextEntry::make('rejected_at')
                            ->label('Fecha de Rechazo')
                            ->dateTime(),
                    ])
                    ->visible(fn (Quotation $record): bool => $record->status === QuotationStatus::Rejected)
                    ->columns(3),
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
                Tables\Columns\TextColumn::make('quotation_number')
                    ->label('Numero')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Valida hasta')
                    ->date()
                    ->sortable()
                    ->color(fn (Quotation $record): string => $record->is_expired ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(QuotationStatus::class)
                    ->label('Estado'),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    })
                    ->label('Rango de Fechas'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Cotización')
                    ->modalDescription('Esta acción aprobará la cotización y el cliente será notificado.')
                    ->visible(fn (Quotation $record): bool => $record->status === QuotationStatus::Pending)
                    ->action(function (Quotation $record) {
                        app(QuotationService::class)->approve($record, auth()->user());
                        Notification::make()->title('Cotización aprobada')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Quotation $record): bool => $record->status === QuotationStatus::Pending)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Razón de Rechazo')
                            ->required()
                            ->placeholder('Indique la razón del rechazo...'),
                    ])
                    ->action(function (Quotation $record, array $data) {
                        app(QuotationService::class)->reject($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Cotización rechazada')->danger()->send();
                    }),
                Tables\Actions\Action::make('convert')
                    ->label('Convertir a Pedido')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Convertir a Pedido')
                    ->modalDescription('Se creara un pedido con los items de esta cotizacion y se descontara el stock.')
                    ->visible(fn (Quotation $record): bool => $record->is_convertible)
                    ->action(function (Quotation $record) {
                        $order = app(QuotationService::class)->convertToOrder($record);
                        Notification::make()
                            ->title('Pedido creado: ' . $order->order_number)
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function (Quotation $record) {
                        $pdf = app(QuotationService::class)->generatePdf($record);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'cotizacion-' . $record->quotation_number . '.pdf');
                    }),
                Tables\Actions\Action::make('admin_notes')
                    ->label('Notas')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('gray')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notas del Admin')
                            ->default(fn (Quotation $record) => $record->admin_notes)
                            ->placeholder('Notas internas...'),
                    ])
                    ->action(function (Quotation $record, array $data) {
                        $record->update(['admin_notes' => $data['admin_notes']]);
                        Notification::make()->title('Notas actualizadas')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'view' => Pages\ViewQuotation::route('/{record}'),
        ];
    }
}
