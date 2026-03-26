<?php

namespace App\Filament\Pages;

use App\Models\GeneralSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageGeneralSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $title = 'Ajustes del Sistema';

    protected static string $view = 'filament.pages.manage-general-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = GeneralSetting::forCurrentTenantOrCreate();

        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label('Nombre del Sistema')
                            ->required(),
                        Forms\Components\Select::make('default_language')
                            ->label('Idioma Predeterminado')
                            ->options([
                                'es' => 'Español',
                                'en' => 'English',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Identidad Visual')
                    ->schema([
                        Forms\Components\FileUpload::make('site_logo')
                            ->label('Logo del Sistema')
                            ->image()
                            ->disk('public')
                            ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/settings')
                            ->visibility('public'),
                        Forms\Components\FileUpload::make('site_favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/settings')
                            ->visibility('public'),
                        Forms\Components\Select::make('theme_color')
                            ->label('Color del Tema')
                            ->options([
                                'indigo' => 'Índigo',
                                'amber' => 'Ámbar',
                                'emerald' => 'Esmeralda',
                                'red' => 'Rojo',
                                'blue' => 'Azul',
                                'slate' => 'Pizarra',
                            ])
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Configuración Fiscal y Moneda')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('tax_rate')
                                    ->label('IVA / Impuesto (%)')
                                    ->helperText('Porcentaje de impuesto aplicado a las compras (ej. 15).')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(15.00)
                                    ->required(),
                                Forms\Components\TextInput::make('currency_code')
                                    ->label('Código de Moneda')
                                    ->helperText('ISO 4217 (ej. USD, EUR)')
                                    ->maxLength(10)
                                    ->default('USD'),
                                Forms\Components\TextInput::make('currency_symbol')
                                    ->label('Símbolo de Moneda')
                                    ->helperText('Ej: $, €, S/.')
                                    ->maxLength(5)
                                    ->default('$'),
                            ]),
                    ]),

                Forms\Components\Section::make('Configuración de Negocio')
                    ->description('Configure plazos, prefijos y umbrales de su tienda.')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('quotation_validity_days')
                                    ->label('Validez de Cotización (días)')
                                    ->helperText('Días que una cotización es válida antes de expirar.')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(365)
                                    ->suffix('días')
                                    ->default(15),
                                Forms\Components\TextInput::make('quotation_prefix')
                                    ->label('Prefijo de Cotización')
                                    ->helperText('Prefijo para el número de cotización (ej. COT, QUOT).')
                                    ->maxLength(10)
                                    ->default('COT'),
                                Forms\Components\TextInput::make('cart_expiration_days')
                                    ->label('Expiración del Carrito (días)')
                                    ->helperText('Días antes de que un carrito inactivo expire.')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(365)
                                    ->suffix('días')
                                    ->default(30),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('abandoned_cart_reminder_hours')
                                    ->label('Recordatorio Carrito Abandonado (horas)')
                                    ->helperText('Horas después de inactividad para enviar recordatorio.')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(168)
                                    ->suffix('horas')
                                    ->default(24),
                                Forms\Components\TextInput::make('low_stock_threshold')
                                    ->label('Umbral de Stock Bajo')
                                    ->helperText('Cantidad mínima para generar alerta de stock bajo.')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(1000)
                                    ->suffix('unidades')
                                    ->default(5),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Registro de Usuarios')
                    ->description('Configure cómo se manejan los nuevos registros de clientes.')
                    ->schema([
                        Forms\Components\Toggle::make('require_account_approval')
                            ->label('Requiere Aprobación de Cuenta')
                            ->helperText('Cuando está activado, los nuevos usuarios deben ser aprobados por un administrador antes de poder iniciar sesión.')
                            ->default(false),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Configuración de Email (SMTP)')
                    ->description('Configure el servidor SMTP para enviar emails desde su tienda. Si no se configura, se usara el servidor por defecto del sistema.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('mail_from_name')
                                    ->label('Nombre del Remitente')
                                    ->placeholder('Mi Tienda'),
                                Forms\Components\TextInput::make('mail_from_address')
                                    ->label('Email del Remitente')
                                    ->email()
                                    ->placeholder('noreply@mitienda.com'),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('smtp_host')
                                    ->label('Servidor SMTP')
                                    ->placeholder('smtp.gmail.com'),
                                Forms\Components\TextInput::make('smtp_port')
                                    ->label('Puerto')
                                    ->numeric()
                                    ->placeholder('587'),
                                Forms\Components\Select::make('smtp_encryption')
                                    ->label('Encriptacion')
                                    ->options([
                                        'tls' => 'TLS',
                                        'ssl' => 'SSL',
                                        'none' => 'Ninguna',
                                    ])
                                    ->placeholder('TLS'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('smtp_username')
                                    ->label('Usuario SMTP')
                                    ->placeholder('usuario@gmail.com'),
                                Forms\Components\TextInput::make('smtp_password')
                                    ->label('Contraseña SMTP')
                                    ->password()
                                    ->revealable(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $settings = GeneralSetting::forCurrentTenantOrCreate();
        $settings->update($this->form->getState());

        Notification::make()
            ->title('Ajustes guardados exitosamente')
            ->success()
            ->send();

        // Ideally we would redirect or reload to apply changes, but for now simple notification.
    }
}
