<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Tenant;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DomainManagement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static string $view = 'filament.super-admin.pages.domain-management';

    protected static ?string $navigationGroup = 'Configuración Global';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Gestion de Dominios';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tenant::query()
                    ->withoutGlobalScopes()
                    ->with('generalSettings')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('Slug'),

                TextColumn::make('domain')
                    ->label('Dominio')
                    ->placeholder('—'),

                TextColumn::make('custom_domain')
                    ->label('Dominio Personalizado')
                    ->state(fn (Tenant $record): ?string => $record->generalSettings?->getDomainConfig()['custom_domain'] ?? null)
                    ->placeholder('—'),

                TextColumn::make('domain_status')
                    ->label('Estado')
                    ->badge()
                    ->state(fn (Tenant $record): string => $record->domain ? 'Configurado' : 'Sin dominio')
                    ->color(fn (string $state): string => match ($state) {
                        'Configurado' => 'success',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->actions([
                Action::make('edit_domain')
                    ->label('Editar Dominio')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        TextInput::make('domain')
                            ->label('Dominio')
                            ->placeholder('ejemplo.com'),
                    ])
                    ->fillForm(fn (Tenant $record): array => [
                        'domain' => $record->domain,
                    ])
                    ->action(function (Tenant $record, array $data): void {
                        $record->update(['domain' => $data['domain']]);

                        Notification::make()
                            ->title('Dominio actualizado')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                TernaryFilter::make('has_domain')
                    ->label('Tiene Dominio')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('domain'),
                        false: fn ($query) => $query->whereNull('domain'),
                    ),
                TernaryFilter::make('is_active')
                    ->label('Activo'),
            ]);
    }
}
