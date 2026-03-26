<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Models\PlatformSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class ManageGlobalSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.super-admin.pages.manage-global-settings';

    protected static ?string $navigationGroup = 'Configuración Global';
    
    protected static ?string $title = 'Ajustes de Plataforma';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = PlatformSetting::first();
        if ($settings) {
            $this->form->fill($settings->toArray());
        } else {
            $this->form->fill();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Branding del SaaS')
                    ->schema([
                        TextInput::make('brand_name')
                            ->label('Nombre de la Marca')
                            ->required(),
                        FileUpload::make('brand_logo')
                            ->label('Logotipo Principal')
                            ->image()
                            ->directory('platform'),
                    ])->columns(2),

                Section::make('Contacto y Soporte')
                    ->schema([
                        TextInput::make('support_email')
                            ->label('Correo de Soporte')
                            ->email(),
                        TextInput::make('support_phone')
                            ->label('Teléfono de Soporte')
                            ->tel(),
                    ])->columns(2),

                Section::make('Políticas y Accesos')
                    ->schema([
                        TextInput::make('terms_of_service_url')
                            ->label('URL Términos de Servicio')
                            ->url(),
                        TextInput::make('privacy_policy_url')
                            ->label('URL Política de Privacidad')
                            ->url(),
                        Toggle::make('allow_new_registrations')
                            ->label('Permitir Nuevos Registros (Tenants)')
                            ->default(true),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = PlatformSetting::first();
        if ($settings) {
            $settings->update($data);
        } else {
            PlatformSetting::create($data);
        }

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }
}
