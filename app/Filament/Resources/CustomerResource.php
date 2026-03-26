<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Mail\AccountApprovedMail;
use App\Models\User;
use App\Services\TenantMailService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Orders;
    }

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static ?string $navigationGroup = 'Gestión de Tienda';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'customers';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where(function (Builder $q) {
                $q->whereNull('sub_role')
                    ->orWhere('sub_role', 'customer');
            })
            ->withCount(['orders', 'wishlists']);

        $user = auth()->user();

        if ($user && ! $user->isSuperAdmin()) {
            $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            }
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Cliente')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel(),
                        Forms\Components\Select::make('identification_type')
                            ->label('Tipo de Identificación')
                            ->options([
                                'cedula' => 'Cédula',
                                'ruc' => 'RUC',
                                'passport' => 'Pasaporte',
                            ]),
                        Forms\Components\TextInput::make('identification_number')
                            ->label('Número de Identificación')
                            ->maxLength(20),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Fecha de Nacimiento'),
                    ])->columns(2),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Cuenta Activa'),
                        Forms\Components\Toggle::make('newsletter_subscribed')
                            ->label('Suscrito al Boletín'),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información del Cliente')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nombre'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Teléfono')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('identification_type')
                            ->label('Tipo ID')
                            ->badge()
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('identification_number')
                            ->label('Número ID')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('date_of_birth')
                            ->label('Fecha de Nacimiento')
                            ->date()
                            ->placeholder('—'),
                    ])->columns(3),

                Infolists\Components\Section::make('Métricas del Cliente')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_orders')
                            ->label('Total Pedidos')
                            ->state(fn (User $record): int => $record->orders()->count()),
                        Infolists\Components\TextEntry::make('total_spent')
                            ->label('Total Gastado')
                            ->state(fn (User $record): string => '$' . number_format($record->orders()->where('payment_status', 'completed')->sum('total'), 2)),
                        Infolists\Components\TextEntry::make('wishlist_items')
                            ->label('Items en Wishlist')
                            ->state(fn (User $record): int => $record->wishlists()->count()),
                        Infolists\Components\TextEntry::make('last_order')
                            ->label('Último Pedido')
                            ->state(fn (User $record): string => $record->orders()->latest()->first()?->created_at?->diffForHumans() ?? '—'),
                    ])->columns(4),

                Infolists\Components\Section::make('Estado de la Cuenta')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Cuenta Activa')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('newsletter_subscribed')
                            ->label('Suscrito al Boletín')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('email_verified_at')
                            ->label('Email Verificado')
                            ->dateTime()
                            ->placeholder('No verificado'),
                        Infolists\Components\TextEntry::make('last_login_at')
                            ->label('Último Acceso')
                            ->dateTime()
                            ->placeholder('Nunca'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Registrado')
                            ->dateTime(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Pedidos')
                    ->counts('orders')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Gastado')
                    ->state(fn (User $record): float => $record->orders()->where('payment_status', 'completed')->sum('total'))
                    ->money('USD')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->withSum([
                        'orders' => fn (Builder $q) => $q->where('payment_status', 'completed'),
                    ], 'total')->orderBy('orders_sum_total', $direction)),
                Tables\Columns\TextColumn::make('wishlists_count')
                    ->label('Wishlist')
                    ->counts('wishlists')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\IconColumn::make('newsletter_subscribed')
                    ->label('Boletín')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Último Acceso')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado'),
                Tables\Filters\Filter::make('pending_approval')
                    ->label('Pendiente de Aprobación')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false))
                    ->toggle(),
                Tables\Filters\TernaryFilter::make('newsletter_subscribed')
                    ->label('Suscrito al Boletín'),
                Tables\Filters\Filter::make('has_orders')
                    ->label('Con Pedidos')
                    ->query(fn (Builder $query): Builder => $query->has('orders')),
                Tables\Filters\Filter::make('no_orders')
                    ->label('Sin Pedidos')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('orders')),
            ])
            ->actions([
                Tables\Actions\Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Cuenta')
                    ->modalDescription(fn (User $record): string => "¿Aprobar la cuenta de {$record->name} ({$record->email})?")
                    ->visible(fn (User $record): bool => ! $record->is_active)
                    ->action(function (User $record): void {
                        $record->update(['is_active' => true]);
                        app(TenantMailService::class)->send(new AccountApprovedMail($record));
                        Notification::make()->title('Cuenta aprobada')->success()->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('aprobar')
                        ->label('Aprobar Cuentas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records): void {
                            $records->each(function (User $user): void {
                                if (! $user->is_active) {
                                    $user->update(['is_active' => true]);
                                    app(TenantMailService::class)->send(new AccountApprovedMail($user));
                                }
                            });
                            Notification::make()->title('Cuentas aprobadas')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('desactivar')
                        ->label('Desactivar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
