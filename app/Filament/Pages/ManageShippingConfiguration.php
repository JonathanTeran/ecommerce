<?php

namespace App\Filament\Pages;

use App\Models\GeneralSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageShippingConfiguration extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $title = 'Configuración de Envíos';

    protected static ?string $navigationLabel = 'Envíos';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.manage-shipping-configuration';

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
        $settings = GeneralSetting::forCurrentTenant();
        $config = $settings?->getShippingConfig() ?? ['carriers' => []];

        $this->form->fill(['shipping_config' => $config]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transportistas y Tarifas')
                    ->description('Configura los transportistas disponibles y sus tarifas de envío.')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Forms\Components\Repeater::make('shipping_config.carriers')
                            ->label('')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre del Transportista')
                                            ->required()
                                            ->placeholder('Ej: Servientrega'),

                                        Forms\Components\TextInput::make('key')
                                            ->label('Código')
                                            ->required()
                                            ->placeholder('Ej: servientrega')
                                            ->helperText('Identificador único, sin espacios.'),

                                        Forms\Components\TextInput::make('tracking_url_template')
                                            ->label('URL de Rastreo')
                                            ->placeholder('https://rastreo.ejemplo.com/{tracking_number}')
                                            ->helperText('Usa {tracking_number} como placeholder')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel(),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Activo')
                                            ->default(true),
                                    ]),

                                Forms\Components\Repeater::make('rates')
                                    ->label('Tarifas')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->placeholder('Ej: Envío Estándar'),

                                        Forms\Components\TextInput::make('price')
                                            ->label('Precio')
                                            ->numeric()
                                            ->required()
                                            ->prefix('$')
                                            ->default(0),

                                        Forms\Components\TextInput::make('estimated_days')
                                            ->label('Días Estimados')
                                            ->placeholder('Ej: 3-5 días'),

                                        Forms\Components\TextInput::make('zone')
                                            ->label('Zona')
                                            ->placeholder('Ej: Nacional'),

                                        Forms\Components\TextInput::make('min_order_amount')
                                            ->label('Monto Mínimo')
                                            ->numeric()
                                            ->prefix('$')
                                            ->helperText('Dejar vacío para sin mínimo'),

                                        Forms\Components\TextInput::make('max_order_amount')
                                            ->label('Monto Máximo')
                                            ->numeric()
                                            ->prefix('$')
                                            ->helperText('Dejar vacío para sin máximo'),

                                        Forms\Components\TextInput::make('min_weight')
                                            ->label('Peso Mín (kg)')
                                            ->numeric(),

                                        Forms\Components\TextInput::make('max_weight')
                                            ->label('Peso Máx (kg)')
                                            ->numeric(),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Activa')
                                            ->default(true),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(0)
                                    ->addActionLabel('Agregar Tarifa')
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => ($state['name'] ?? '') . (isset($state['price']) ? ' - $' . number_format((float) $state['price'], 2) : '')),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Agregar Transportista')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => ($state['name'] ?? 'Nuevo Transportista') . (($state['is_active'] ?? true) ? '' : ' (inactivo)')),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = GeneralSetting::forCurrentTenantOrCreate();

        $settings->update([
            'shipping_config' => $data['shipping_config'] ?? ['carriers' => []],
        ]);

        Notification::make()
            ->title('Configuración de envíos guardada')
            ->success()
            ->send();
    }
}
