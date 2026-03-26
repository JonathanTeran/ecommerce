<?php

namespace App\Filament\Pages;

use App\Models\GeneralSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSocialLogin extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $title = 'Login Social';

    protected static ?string $navigationLabel = 'Login Social';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.manage-social-login';

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
            'social_login_config' => $settings->getSocialLoginConfig(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Autenticación Social')
                    ->description('Configure las credenciales de OAuth para permitir que sus clientes inicien sesión con sus cuentas de redes sociales.')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        // Google
                        Forms\Components\Fieldset::make('Google')
                            ->schema([
                                Forms\Components\Toggle::make('social_login_config.google.enabled')
                                    ->label('Activar Google Login')
                                    ->live()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('social_login_config.google.client_id')
                                    ->label('Client ID')
                                    ->visible(fn (Forms\Get $get) => $get('social_login_config.google.enabled')),
                                Forms\Components\TextInput::make('social_login_config.google.client_secret')
                                    ->label('Client Secret')
                                    ->password()
                                    ->revealable()
                                    ->visible(fn (Forms\Get $get) => $get('social_login_config.google.enabled')),
                            ])->columns(2),

                        // Facebook
                        Forms\Components\Fieldset::make('Facebook')
                            ->schema([
                                Forms\Components\Toggle::make('social_login_config.facebook.enabled')
                                    ->label('Activar Facebook Login')
                                    ->live()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('social_login_config.facebook.client_id')
                                    ->label('App ID')
                                    ->visible(fn (Forms\Get $get) => $get('social_login_config.facebook.enabled')),
                                Forms\Components\TextInput::make('social_login_config.facebook.client_secret')
                                    ->label('App Secret')
                                    ->password()
                                    ->revealable()
                                    ->visible(fn (Forms\Get $get) => $get('social_login_config.facebook.enabled')),
                            ])->columns(2),

                        // Apple
                        Forms\Components\Fieldset::make('Apple')
                            ->schema([
                                Forms\Components\Toggle::make('social_login_config.apple.enabled')
                                    ->label('Activar Apple Login')
                                    ->live()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('social_login_config.apple.client_id')
                                    ->label('Service ID')
                                    ->visible(fn (Forms\Get $get) => $get('social_login_config.apple.enabled')),
                                Forms\Components\TextInput::make('social_login_config.apple.client_secret')
                                    ->label('Key / Secret')
                                    ->password()
                                    ->revealable()
                                    ->visible(fn (Forms\Get $get) => $get('social_login_config.apple.enabled')),
                            ])->columns(2),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = GeneralSetting::forCurrentTenantOrCreate();

        $settings->update([
            'social_login_config' => $data['social_login_config'] ?? [],
        ]);

        Notification::make()
            ->title('Configuración de login social guardada')
            ->success()
            ->send();
    }
}
