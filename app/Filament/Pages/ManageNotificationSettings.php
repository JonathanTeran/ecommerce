<?php

namespace App\Filament\Pages;

use App\Enums\Module;
use App\Models\EmailTemplate;
use App\Models\GeneralSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageNotificationSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $title = 'Notificaciones';

    protected static ?string $navigationLabel = 'Notificaciones';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.manage-notification-settings';

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
        $config = $settings?->getNotificationConfig() ?? [];

        $this->form->fill(['notification_config' => $config]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Notificaciones al Cliente')
                    ->description('Configura qué emails se envían automáticamente a los clientes.')
                    ->schema([
                        Forms\Components\Toggle::make('notification_config.order_confirmed')
                            ->label('Pedido Confirmado')
                            ->helperText('Email cuando se confirma un pedido'),
                        Forms\Components\Toggle::make('notification_config.order_shipped')
                            ->label('Pedido Enviado')
                            ->helperText('Email cuando el pedido es enviado con tracking'),
                        Forms\Components\Toggle::make('notification_config.order_delivered')
                            ->label('Pedido Entregado')
                            ->helperText('Email cuando el pedido es entregado'),
                        Forms\Components\Toggle::make('notification_config.order_cancelled')
                            ->label('Pedido Cancelado')
                            ->helperText('Email cuando se cancela un pedido'),
                        Forms\Components\Toggle::make('notification_config.payment_received')
                            ->label('Pago Recibido')
                            ->helperText('Email de confirmación de pago'),
                        Forms\Components\Toggle::make('notification_config.refund_processed')
                            ->label('Reembolso Procesado')
                            ->helperText('Email cuando se procesa un reembolso'),
                        Forms\Components\Toggle::make('notification_config.welcome_email')
                            ->label('Email de Bienvenida')
                            ->helperText('Email al registrarse un nuevo cliente'),
                    ])->columns(2),

                Forms\Components\Section::make('Notificaciones de Marketing')
                    ->description('Emails automatizados para recuperación y engagement.')
                    ->schema([
                        Forms\Components\Toggle::make('notification_config.abandoned_cart')
                            ->label('Carrito Abandonado')
                            ->helperText('Recordatorio automático de carritos abandonados'),
                        Forms\Components\Toggle::make('notification_config.review_request')
                            ->label('Solicitud de Reseña')
                            ->helperText('Email solicitando reseña después de la entrega'),
                    ])->columns(2),

                Forms\Components\Section::make('Notificaciones al Admin')
                    ->description('Alertas que recibe el equipo administrador.')
                    ->schema([
                        Forms\Components\Toggle::make('notification_config.new_order_admin')
                            ->label('Nuevo Pedido')
                            ->helperText('Alerta cuando entra un nuevo pedido'),
                        Forms\Components\Toggle::make('notification_config.low_stock_admin')
                            ->label('Stock Bajo')
                            ->helperText('Alerta cuando un producto tiene stock bajo'),
                        Forms\Components\Toggle::make('notification_config.new_return_admin')
                            ->label('Nueva Devolución')
                            ->helperText('Alerta cuando se solicita una devolución'),
                        Forms\Components\Toggle::make('notification_config.new_ticket_admin')
                            ->label('Nuevo Ticket de Soporte')
                            ->helperText('Alerta cuando se crea un ticket de soporte'),
                    ])->columns(2),

                Forms\Components\Section::make('Plantillas de Email Disponibles')
                    ->description('Las plantillas de email son gestionadas a nivel de plataforma. Aquí puedes ver las que están disponibles.')
                    ->schema([
                        Forms\Components\Placeholder::make('templates_info')
                            ->content(function (): string {
                                $templates = EmailTemplate::active()->get();
                                if ($templates->isEmpty()) {
                                    return 'No hay plantillas de email configuradas.';
                                }

                                return $templates->map(fn ($t) => "• **{$t->name}** — {$t->subject}")->implode("\n");
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = GeneralSetting::forCurrentTenantOrCreate();

        $settings->update([
            'notification_config' => $data['notification_config'] ?? [],
        ]);

        Notification::make()
            ->title('Configuración de notificaciones guardada')
            ->success()
            ->send();
    }
}
