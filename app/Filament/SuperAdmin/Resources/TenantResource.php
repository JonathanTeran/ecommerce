<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\TenantResource\Pages;
use App\Filament\SuperAdmin\Resources\TenantResource\RelationManagers;
use App\Models\Plan;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Gestión SaaS';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacion del Tenant')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Nombre'),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('domain')
                            ->label('Dominio')
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('theme_color')
                            ->options([
                                'indigo' => 'Indigo',
                                'blue' => 'Blue',
                                'emerald' => 'Emerald',
                                'amber' => 'Amber',
                                'red' => 'Red',
                                'slate' => 'Slate',
                            ])
                            ->default('indigo'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->live(),
                        Forms\Components\Toggle::make('is_demo')
                            ->label('Tenant de Demo')
                            ->helperText('Crea un diseno de homepage predeterminado con todas las secciones.')
                            ->default(false)
                            ->visibleOn('create'),
                        Forms\Components\Toggle::make('is_default')
                            ->label('Tenant por Defecto')
                            ->helperText('Se usa cuando no se detecta un dominio especifico (ej. localhost). Solo un tenant puede ser el predeterminado.')
                            ->default(false)
                            ->afterStateUpdated(function (bool $state, ?Tenant $record): void {
                                if ($state) {
                                    Tenant::where('id', '!=', $record?->id)->where('is_default', true)->update(['is_default' => false]);
                                }
                            })
                            ->live(),
                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('Fin del periodo de prueba'),
                        Forms\Components\Textarea::make('suspension_message')
                            ->label('Mensaje de suspension')
                            ->helperText('Mensaje que vera el tenant al intentar acceder. Si se deja vacio se mostrara un mensaje por defecto.')
                            ->rows(3)
                            ->visible(fn (Forms\Get $get): bool => ! $get('is_active'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Localización')
                    ->schema([
                        Forms\Components\Select::make('country')
                            ->label('País')
                            ->options([
                                'mx' => 'México',
                                'us' => 'Estados Unidos',
                                'es' => 'España',
                                'co' => 'Colombia',
                                'ar' => 'Argentina',
                                'cl' => 'Chile',
                                'pe' => 'Perú',
                            ])
                            ->searchable(),
                        Forms\Components\Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'MXN' => 'Peso Mexicano (MXN)',
                                'USD' => 'Dólar Estadounidense (USD)',
                                'EUR' => 'Euro (EUR)',
                                'COP' => 'Peso Colombiano (COP)',
                                'ARS' => 'Peso Argentino (ARS)',
                                'CLP' => 'Peso Chileno (CLP)',
                                'PEN' => 'Sol Peruano (PEN)',
                            ])
                            ->searchable(),
                        Forms\Components\Select::make('language')
                            ->label('Idioma')
                            ->options([
                                'es' => 'Español',
                                'en' => 'Inglés',
                            ])
                            ->default('es'),
                        Forms\Components\Select::make('timezone')
                            ->label('Zona Horaria')
                            ->options([
                                'America/Mexico_City' => 'America/Mexico_City',
                                'America/New_York' => 'America/New_York',
                                'America/Bogota' => 'America/Bogota',
                                'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
                                'America/Santiago' => 'America/Santiago',
                                'America/Lima' => 'America/Lima',
                                'Europe/Madrid' => 'Europe/Madrid',
                            ])
                            ->searchable(),
                    ])->columns(2),

                Forms\Components\Section::make('Plan')
                    ->schema([
                        Forms\Components\Select::make('plan_id')
                            ->label('Plan')
                            ->options(Plan::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->visibleOn('create'),
                    ]),

                Forms\Components\Section::make('Administrador Inicial')
                    ->description('Se creara un usuario administrador para este tenant')
                    ->schema([
                        Forms\Components\TextInput::make('admin_name')
                            ->label('Nombre del Admin')
                            ->required(),
                        Forms\Components\TextInput::make('admin_email')
                            ->label('Email del Admin')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('admin_password')
                            ->label('Password del Admin')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ])->columns(3)
                    ->visibleOn('create'),
            ]);
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Información Principal')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('name')
                            ->label('Nombre de la Tienda'),
                        \Filament\Infolists\Components\TextEntry::make('domain')
                            ->label('Dominio Público'),
                        \Filament\Infolists\Components\TextEntry::make('activeSubscription.plan.name')
                            ->label('Plan Actual')
                            ->badge(),
                        \Filament\Infolists\Components\IconEntry::make('is_active')
                            ->label('Estado Activo')
                            ->boolean(),
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->label('Fecha de Alta')
                            ->dateTime(),
                    ])->columns(3),
                    
                \Filament\Infolists\Components\Section::make('Métricas Operativas (Vista 360)')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('users_count')
                            ->label('Usuarios Administrativos')
                            ->state(function (Tenant $record): int {
                                return $record->users()->count();
                            }),
                        \Filament\Infolists\Components\TextEntry::make('products_count')
                            ->label('Total Productos')
                            ->state(function (Tenant $record): int {
                                return $record->products()->count();
                            }),
                        \Filament\Infolists\Components\TextEntry::make('orders_count')
                            ->label('Total Pedidos')
                            ->state(function (Tenant $record): int {
                                return $record->orders()->count();
                            }),
                        \Filament\Infolists\Components\TextEntry::make('gmv')
                            ->label('GMV (Ventas Brutas)')
                            ->state(function (Tenant $record): string {
                                $total = $record->orders()->where('payment_status', 'completed')->sum('total');
                                return '$' . number_format($total, 2);
                            }),
                    ])->columns(4),
                    
                \Filament\Infolists\Components\Section::make('Localización y Configuración')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('country')
                            ->label('País')
                            ->formatStateUsing(fn (?string $state): string => match($state) {
                                'mx' => 'México', 'us' => 'Estados Unidos', 'es' => 'España', 'co' => 'Colombia', 'ar' => 'Argentina', 'cl' => 'Chile', 'pe' => 'Perú', default => $state ?? '-'
                            }),
                        \Filament\Infolists\Components\TextEntry::make('currency')
                            ->label('Moneda Base'),
                        \Filament\Infolists\Components\TextEntry::make('timezone')
                            ->label('Zona Horaria'),
                        \Filament\Infolists\Components\TextEntry::make('language')
                            ->label('Idioma'),
                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['users', 'activeSubscription.plan']))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('domain')
                    ->label('Dominio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->label('País')
                    ->searchable()
                    ->formatStateUsing(fn (?string $state): string => match($state) {
                        'mx' => 'México', 'us' => 'Estados Unidos', 'es' => 'España', 'co' => 'Colombia', 'ar' => 'Argentina', 'cl' => 'Chile', 'pe' => 'Perú', default => $state ?? '-'
                    }),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Moneda')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activeSubscription.plan.name')
                    ->label('Plan')
                    ->badge(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Usuarios'),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Productos'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_demo')
                    ->label('Demo')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deactivated_at')
                    ->label('Desactivado')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                \STS\FilamentImpersonate\Tables\Actions\Impersonate::make('impersonate')
                    ->record(function (Tenant $record) {
                        return $record->users()->first();
                    })
                    ->badge(function (Tenant $record) {
                        return $record->users()->exists() ? null : 'No users';
                    })
                    ->visible(fn (Tenant $record) => $record->users()->exists()),
                Tables\Actions\Action::make('purge')
                    ->label('Purgar datos')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Purgar tenant y todos sus datos')
                    ->modalDescription(fn (Tenant $record): string => "Se eliminará permanentemente el tenant \"{$record->name}\" y todos sus datos (usuarios, productos, pedidos, etc.). Esta acción no se puede deshacer.")
                    ->modalSubmitActionLabel('Sí, purgar permanentemente')
                    ->visible(fn (Tenant $record): bool => $record->isPendingPurge())
                    ->action(function (Tenant $record): void {
                        $record->users()->forceDelete();
                        $record->products()->forceDelete();
                        $record->categories()->forceDelete();
                        $record->brands()->forceDelete();
                        $record->orders()->forceDelete();
                        $record->subscriptions()->delete();
                        $record->generalSettings()->delete();
                        $record->forceDelete();

                        Notification::make()
                            ->title('Tenant purgado exitosamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activar')
                        ->label('Activar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(fn (Tenant $record) => $record->update([
                                'is_active' => true,
                                'deactivated_at' => null,
                            ]));

                            Notification::make()
                                ->title('Tenants activados exitosamente')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('desactivar')
                        ->label('Desactivar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(fn (Tenant $record) => $record->deactivate());

                            Notification::make()
                                ->title('Tenants desactivados exitosamente')
                                ->warning()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
