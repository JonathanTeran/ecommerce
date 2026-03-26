<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Tenant;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TenantUsageMonitor extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string $view = 'filament.super-admin.pages.tenant-usage-monitor';

    protected static ?string $navigationGroup = 'Gestión SaaS';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Monitor de Uso';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tenant::query()
                    ->withoutGlobalScopes()
                    ->with(['activeSubscription.plan', 'products', 'users'])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('activeSubscription.plan.name')
                    ->label('Plan')
                    ->badge()
                    ->default('Sin plan'),

                TextColumn::make('products_usage')
                    ->label('Productos')
                    ->state(function (Tenant $record): string {
                        $count = $record->products->count();
                        $max = $record->activeSubscription?->plan?->max_products;

                        return $count . ' / ' . ($max ?? "\u{221E}");
                    }),

                TextColumn::make('users_usage')
                    ->label('Usuarios')
                    ->state(function (Tenant $record): string {
                        $count = $record->users->count();
                        $max = $record->activeSubscription?->plan?->max_users;

                        return $count . ' / ' . ($max ?? "\u{221E}");
                    }),

                TextColumn::make('orders_count')
                    ->label('Ordenes')
                    ->counts('orders')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->defaultSort('name');
    }
}
