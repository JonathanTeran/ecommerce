<?php

namespace App\Filament\Pages;

use App\Enums\ThemeTemplate;
use App\Models\GeneralSetting;
use App\Models\StoreTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SetupWizard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $title = 'Configurar Mi Tienda';

    protected static ?string $slug = 'setup-wizard';

    protected static string $view = 'filament.pages.setup-wizard';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant || ($tenant->settings['setup_completed'] ?? false)) {
            $this->redirect(route('filament.admin.pages.dashboard'));

            return;
        }

        $settings = GeneralSetting::forCurrentTenantOrCreate();

        $this->form->fill([
            'site_name' => $settings->site_name ?? $tenant->name,
            'theme_template' => $tenant->theme_template?->value ?? 'default',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Tu Tienda')
                        ->icon('heroicon-o-building-storefront')
                        ->description('Informacion basica de tu negocio')
                        ->schema([
                            Forms\Components\TextInput::make('site_name')
                                ->label('Nombre de tu Tienda')
                                ->required()
                                ->maxLength(100),
                            Forms\Components\Textarea::make('description')
                                ->label('Descripcion corta')
                                ->placeholder('Describe tu tienda en una frase...')
                                ->rows(2)
                                ->maxLength(200),
                            Forms\Components\FileUpload::make('site_logo')
                                ->label('Logo (opcional)')
                                ->image()
                                ->disk('public')
                                ->directory(fn () => 'tenant-'.(app()->bound('current_tenant') ? app('current_tenant')->id : 'shared').'/settings')
                                ->visibility('public'),
                        ]),

                    Forms\Components\Wizard\Step::make('Elige tu Estilo')
                        ->icon('heroicon-o-paint-brush')
                        ->description('Selecciona como se vera tu tienda')
                        ->schema([
                            Forms\Components\Radio::make('theme_template')
                                ->label('Estilo Visual')
                                ->options(collect(ThemeTemplate::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]))
                                ->descriptions(collect(ThemeTemplate::cases())->mapWithKeys(fn ($t) => [$t->value => $t->description()]))
                                ->default('default')
                                ->columns(2),
                            Forms\Components\Select::make('store_template_id')
                                ->label('O elige una plantilla predefinida')
                                ->options(fn () => StoreTemplate::active()
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->mapWithKeys(fn ($t) => [$t->id => $t->name.' — '.$t->category_label])
                                )
                                ->placeholder('Ninguna (usar estilo base)')
                                ->searchable()
                                ->nullable(),
                        ]),

                    Forms\Components\Wizard\Step::make('Contacto')
                        ->icon('heroicon-o-phone')
                        ->description('Como te contactan tus clientes')
                        ->schema([
                            Forms\Components\TextInput::make('whatsapp_number')
                                ->label('Numero de WhatsApp')
                                ->placeholder('+593 99 123 4567')
                                ->tel(),
                            Forms\Components\TextInput::make('contact_email')
                                ->label('Email de Contacto')
                                ->email()
                                ->placeholder('ventas@tutienda.com'),
                        ]),

                    Forms\Components\Wizard\Step::make('Listo')
                        ->icon('heroicon-o-check-circle')
                        ->description('Tu tienda esta lista')
                        ->schema([
                            Forms\Components\Placeholder::make('success_message')
                                ->label('')
                                ->content(new \Illuminate\Support\HtmlString(
                                    '<div style="text-align: center; padding: 24px;">'
                                    .'<div style="font-size: 48px; margin-bottom: 16px;">🎉</div>'
                                    .'<h2 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">Tu tienda esta lista</h2>'
                                    .'<p style="color: #6b7280;">Haz click en "Finalizar" para empezar a vender. Puedes personalizar todo desde el panel de administracion.</p>'
                                    .'</div>'
                                )),
                        ]),
                ])
                    ->submitAction(new \Illuminate\Support\HtmlString(
                        '<button type="submit" class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50" style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);">'
                        .'Finalizar Configuracion'
                        .'</button>'
                    )),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $state = $this->form->getState();
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            return;
        }

        // Update settings
        $settings = GeneralSetting::forCurrentTenantOrCreate();
        $settings->update(array_filter([
            'site_name' => $state['site_name'] ?? null,
            'site_logo' => $state['site_logo'] ?? null,
            'social_links' => array_filter([
                'whatsapp_number' => $state['whatsapp_number'] ?? null,
            ]),
        ]));

        // Apply template
        $tenant->update([
            'theme_template' => $state['theme_template'] ?? 'default',
            'store_template_id' => $state['store_template_id'] ?? null,
            'settings' => array_merge($tenant->settings ?? [], ['setup_completed' => true]),
        ]);

        Notification::make()
            ->title('¡Tu tienda esta lista!')
            ->body('Ya puedes empezar a agregar productos y personalizar tu tienda.')
            ->success()
            ->duration(8000)
            ->send();

        $this->redirect(route('filament.admin.pages.dashboard'));
    }

    public static function canAccess(): bool
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        return $tenant && ! ($tenant->settings['setup_completed'] ?? false);
    }
}
