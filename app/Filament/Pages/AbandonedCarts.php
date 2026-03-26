<?php

namespace App\Filament\Pages;

use App\Enums\Module;
use App\Models\Cart;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AbandonedCarts extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $title = 'Carritos Abandonados';

    protected static ?string $navigationLabel = 'Carritos Abandonados';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.abandoned-carts';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            return false;
        }

        return $tenant->hasModule(Module::Cart);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Cart::query()
            ->has('items')
            ->where(function (Builder $q) {
                $q->where('expires_at', '<', now())
                    ->orWhere('updated_at', '<', now()->subHours(2));
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public function getStats(): array
    {
        $baseQuery = Cart::query()->has('items');

        $abandonedQuery = (clone $baseQuery)->where(function (Builder $q) {
            $q->where('expires_at', '<', now())
                ->orWhere('updated_at', '<', now()->subHours(2));
        });

        $totalAbandoned = $abandonedQuery->count();

        $totalValue = 0;
        $abandonedQuery->with('items.product')->each(function (Cart $cart) use (&$totalValue) {
            $totalValue += $cart->items->sum(fn ($item) => $item->price * $item->quantity);
        });

        $remindedCount = (clone $baseQuery)->whereNotNull('reminder_sent_at')->count();

        return [
            'total_abandoned' => $totalAbandoned,
            'total_value' => $totalValue,
            'reminded' => $remindedCount,
            'active_carts' => (clone $baseQuery)->where('expires_at', '>', now())->count(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Cart::query()
                    ->has('items')
                    ->with(['user', 'items.product'])
                    ->where(function (Builder $q) {
                        $q->where('expires_at', '<', now())
                            ->orWhere('updated_at', '<', now()->subHours(2));
                    })
            )
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Cliente')
                    ->placeholder('Invitado')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('cart_total')
                    ->label('Valor')
                    ->state(fn (Cart $record): float => $record->items->sum(fn ($item) => $item->price * $item->quantity))
                    ->money('USD')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query),
                TextColumn::make('reminder_count')
                    ->label('Recordatorios')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state < 3 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('reminder_sent_at')
                    ->label('Último Recordatorio')
                    ->dateTime()
                    ->placeholder('Nunca')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Última Actividad')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expiración')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('with_user')
                    ->label('Con cuenta')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('user_id')),
                Filter::make('guest')
                    ->label('Invitados')
                    ->query(fn (Builder $query): Builder => $query->whereNull('user_id')),
                Filter::make('not_reminded')
                    ->label('Sin recordatorio')
                    ->query(fn (Builder $query): Builder => $query->whereNull('reminder_sent_at')),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view_items')
                    ->label('Ver Items')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Items del Carrito')
                    ->modalContent(fn (Cart $record) => view('filament.pages.partials.cart-items', ['cart' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
                \Filament\Tables\Actions\Action::make('send_reminder')
                    ->label('Enviar Recordatorio')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->visible(fn (Cart $record): bool => $record->user !== null)
                    ->requiresConfirmation()
                    ->modalHeading('Enviar recordatorio de carrito abandonado')
                    ->modalDescription(fn (Cart $record): string => "Se enviará un email de recordatorio a {$record->user?->email}")
                    ->action(function (Cart $record): void {
                        $record->update([
                            'reminder_sent_at' => now(),
                            'reminder_count' => $record->reminder_count + 1,
                        ]);

                        Notification::make()
                            ->title('Recordatorio registrado')
                            ->body("Recordatorio marcado para {$record->user->email}")
                            ->success()
                            ->send();
                    }),
                \Filament\Tables\Actions\Action::make('delete_cart')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Cart $record): void {
                        $record->items()->delete();
                        $record->delete();

                        Notification::make()
                            ->title('Carrito eliminado')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\BulkAction::make('send_reminders')
                        ->label('Enviar Recordatorios')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records): void {
                            $sent = 0;
                            $records->each(function (Cart $cart) use (&$sent) {
                                if ($cart->user) {
                                    $cart->update([
                                        'reminder_sent_at' => now(),
                                        'reminder_count' => $cart->reminder_count + 1,
                                    ]);
                                    $sent++;
                                }
                            });

                            Notification::make()
                                ->title("Recordatorios registrados: {$sent}")
                                ->success()
                                ->send();
                        }),
                    \Filament\Tables\Actions\BulkAction::make('delete_carts')
                        ->label('Eliminar Carritos')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records): void {
                            $records->each(function (Cart $cart) {
                                $cart->items()->delete();
                                $cart->delete();
                            });

                            Notification::make()
                                ->title('Carritos eliminados')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
