<?php

namespace App\Filament\Pages;

use App\Models\LoyaltyProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageLoyaltyProgram extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $title = 'Programa de Fidelización';

    protected static ?string $navigationLabel = 'Fidelización';

    protected static ?int $navigationSort = 7;

    protected static string $view = 'filament.pages.manage-loyalty-program';

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
        $program = LoyaltyProgram::forCurrentTenant();

        $this->form->fill($program ? $program->toArray() : [
            'name' => 'Programa de Puntos',
            'points_per_dollar' => 1.00,
            'redemption_rate' => 0.01,
            'minimum_redemption_points' => 100,
            'is_active' => false,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Programa de Fidelización')
                    ->description('Configure el programa de puntos para recompensar a sus clientes por sus compras.')
                    ->icon('heroicon-o-star')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Programa Activo')
                            ->helperText('Active o desactive el programa de puntos.')
                            ->live()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Programa')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('is_active')),
                        Forms\Components\TextInput::make('points_per_dollar')
                            ->label('Puntos por Dólar')
                            ->numeric()
                            ->required()
                            ->default(1.00)
                            ->helperText('Cuántos puntos gana el cliente por cada dólar gastado.')
                            ->visible(fn (Forms\Get $get) => $get('is_active')),
                        Forms\Components\TextInput::make('redemption_rate')
                            ->label('Valor de Canje (USD por punto)')
                            ->numeric()
                            ->required()
                            ->default(0.01)
                            ->helperText('Cuántos dólares vale cada punto al canjear. Ej: 0.01 = 100 puntos = $1.00')
                            ->visible(fn (Forms\Get $get) => $get('is_active')),
                        Forms\Components\TextInput::make('minimum_redemption_points')
                            ->label('Mínimo de Puntos para Canjear')
                            ->numeric()
                            ->required()
                            ->default(100)
                            ->helperText('Cantidad mínima de puntos que el cliente debe tener para canjear.')
                            ->visible(fn (Forms\Get $get) => $get('is_active')),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        if (! $tenant) {
            Notification::make()->title('Error: no se encontró el tenant.')->danger()->send();

            return;
        }

        LoyaltyProgram::withoutGlobalScopes()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            $data
        );

        Notification::make()
            ->title('Programa de fidelización guardado')
            ->success()
            ->send();
    }
}
