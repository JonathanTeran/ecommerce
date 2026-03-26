<?php

namespace App\Filament\Pages;

use App\Models\GeneralSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManagePaymentGateways extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $title = 'Pasarelas de Pago';

    protected static ?string $navigationLabel = 'Pasarelas de Pago';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.manage-payment-gateways';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        return $tenant !== null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $settings = GeneralSetting::forCurrentTenantOrCreate();

        $this->form->fill([
            'payment_gateways_config' => $settings->getPaymentGatewaysConfig(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cotizaciones')
                    ->description('Configure si su tienda permite solicitar cotizaciones.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Toggle::make('payment_gateways_config.quotations_enabled')
                            ->label('Habilitar Cotizaciones')
                            ->helperText('Cuando está desactivado, los clientes no podrán solicitar cotizaciones desde la tienda. Las cotizaciones existentes en el panel seguirán siendo accesibles.')
                            ->default(true)
                            ->live()
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('payment_gateways_config.quotation_only_mode')
                            ->label('Solo Cotizaciones')
                            ->helperText('Cuando está activo, el checkout solo permite solicitar cotizaciones. No se mostrarán métodos de pago ni se podrán realizar compras directas.')
                            ->live()
                            ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.quotations_enabled'))
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pasarelas de Pago')
                    ->description('Configure las credenciales de sus pasarelas de pago. Cada tienda puede tener sus propias credenciales.')
                    ->icon('heroicon-o-credit-card')
                    ->visible(fn (Forms\Get $get) => ! $get('payment_gateways_config.quotation_only_mode'))
                    ->schema([
                        // --- Nuvei ---
                        Forms\Components\Fieldset::make('Nuvei (Tarjetas de Credito/Debito)')
                            ->schema([
                                Forms\Components\Toggle::make('payment_gateways_config.nuvei_enabled')
                                    ->label('Activar Nuvei')
                                    ->live()
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('payment_gateways_config.nuvei_environment')
                                    ->label('Entorno')
                                    ->options(['test' => 'Pruebas', 'production' => 'Produccion'])
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.nuvei_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.nuvei_merchant_id')
                                    ->label('Merchant ID')
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.nuvei_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.nuvei_site_id')
                                    ->label('Site ID')
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.nuvei_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.nuvei_secret_key')
                                    ->label('Secret Key')
                                    ->password()
                                    ->revealable()
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.nuvei_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.nuvei_surcharge_percentage')
                                    ->label('Recargo (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->helperText('Recargo adicional por usar tarjeta.')
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.nuvei_enabled')),
                            ])->columns(2),

                        // --- PayPhone ---
                        Forms\Components\Fieldset::make('PayPhone')
                            ->schema([
                                Forms\Components\Toggle::make('payment_gateways_config.payphone_enabled')
                                    ->label('Activar PayPhone')
                                    ->live()
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('payment_gateways_config.payphone_environment')
                                    ->label('Entorno')
                                    ->options(['test' => 'Pruebas', 'production' => 'Produccion'])
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.payphone_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.payphone_token')
                                    ->label('Token de Autorizacion')
                                    ->password()
                                    ->revealable()
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.payphone_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.payphone_store_id')
                                    ->label('Store ID')
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.payphone_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.payphone_surcharge_percentage')
                                    ->label('Recargo (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->helperText('Recargo adicional por usar PayPhone.')
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.payphone_enabled')),
                            ])->columns(2),

                        // --- Kushki / Datafast ---
                        Forms\Components\Fieldset::make('Kushki / Datafast')
                            ->schema([
                                Forms\Components\Toggle::make('payment_gateways_config.kushki_enabled')
                                    ->label('Activar Kushki / Datafast')
                                    ->live()
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('payment_gateways_config.kushki_environment')
                                    ->label('Entorno')
                                    ->options(['test' => 'Pruebas', 'production' => 'Produccion'])
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.kushki_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.kushki_public_key')
                                    ->label('Public Key')
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.kushki_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.kushki_private_key')
                                    ->label('Private Key')
                                    ->password()
                                    ->revealable()
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.kushki_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.kushki_surcharge_percentage')
                                    ->label('Recargo (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->helperText('Recargo adicional por usar Kushki.')
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.kushki_enabled')),
                            ])->columns(2),

                        // --- Transferencia Bancaria ---
                        Forms\Components\Fieldset::make('Transferencia Bancaria')
                            ->schema([
                                Forms\Components\Toggle::make('payment_gateways_config.bank_transfer_enabled')
                                    ->label('Activar Transferencia Bancaria')
                                    ->live()
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('payment_gateways_config.bank_transfer_instructions')
                                    ->label('Instrucciones de Pago')
                                    ->helperText('Incluya numero de cuenta, banco, beneficiario, etc.')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.bank_transfer_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.bank_transfer_surcharge_percentage')
                                    ->label('Recargo (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.bank_transfer_enabled')),
                                Forms\Components\Toggle::make('payment_gateways_config.bank_transfer_requires_proof')
                                    ->label('Requiere Comprobante de Pago')
                                    ->default(true)
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.bank_transfer_enabled')),
                            ])->columns(2),

                        // --- Pago Contra Entrega ---
                        Forms\Components\Fieldset::make('Pago Contra Entrega')
                            ->schema([
                                Forms\Components\Toggle::make('payment_gateways_config.cash_on_delivery_enabled')
                                    ->label('Activar Pago Contra Entrega')
                                    ->live()
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('payment_gateways_config.cash_on_delivery_instructions')
                                    ->label('Instrucciones')
                                    ->helperText('Ej: Solo disponible en Quito, monto maximo $500.')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.cash_on_delivery_enabled')),
                                Forms\Components\TextInput::make('payment_gateways_config.cash_on_delivery_surcharge_percentage')
                                    ->label('Recargo (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.cash_on_delivery_enabled')),
                                Forms\Components\Toggle::make('payment_gateways_config.cash_on_delivery_requires_proof')
                                    ->label('Requiere Comprobante de Pago')
                                    ->default(false)
                                    ->visible(fn (Forms\Get $get) => $get('payment_gateways_config.cash_on_delivery_enabled')),
                            ])->columns(2),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $config = $data['payment_gateways_config'] ?? [];

        if (! ($config['quotations_enabled'] ?? true)) {
            $config['quotation_only_mode'] = false;
        }

        $settings = GeneralSetting::forCurrentTenantOrCreate();

        $settings->update([
            'payment_gateways_config' => $config,
        ]);

        Notification::make()
            ->title('Configuración de pasarelas de pago guardada')
            ->success()
            ->send();
    }
}
