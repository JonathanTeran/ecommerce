<?php

namespace App\Filament\Buyer\Pages;

use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTransaction;
use App\Services\LoyaltyService;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class LoyaltyPoints extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $title = 'Mis Puntos';

    protected static ?string $navigationLabel = 'Mis Puntos';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.buyer.pages.loyalty-points';

    public static function canAccess(): bool
    {
        $loyaltyService = app(LoyaltyService::class);

        return $loyaltyService->isActive();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getPointsBalance(): int
    {
        return auth()->user()->points_balance ?? 0;
    }

    public function getRedemptionValue(): string
    {
        $service = app(LoyaltyService::class);
        $value = $service->calculateRedemptionValue($this->getPointsBalance());

        return '$' . number_format($value, 2);
    }

    public function getProgramName(): string
    {
        $program = LoyaltyProgram::forCurrentTenant();

        return $program?->name ?? 'Programa de Puntos';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LoyaltyTransaction::query()
                    ->where('user_id', auth()->id())
                    ->orderByDesc('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'earned' => 'Ganados',
                        'redeemed' => 'Canjeados',
                        'adjusted' => 'Ajuste',
                        'expired' => 'Expirados',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'earned' => 'success',
                        'redeemed' => 'warning',
                        'adjusted' => 'info',
                        'expired' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('points')
                    ->label('Puntos')
                    ->formatStateUsing(fn (int $state) => ($state > 0 ? '+' : '') . number_format($state))
                    ->color(fn (int $state) => $state >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Saldo')
                    ->formatStateUsing(fn (int $state) => number_format($state)),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
