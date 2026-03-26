<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\AuditLogResource\Pages;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class AuditLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Seguridad y Auditoría';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return $user && $user->isSuperAdmin() && in_array($user->sub_role, ['owner', 'compliance', 'security', null]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detalles del Evento')
                    ->schema([
                        Infolists\Components\TextEntry::make('log_name')
                            ->label('Tipo')
                            ->badge(),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Descripcion'),
                        Infolists\Components\TextEntry::make('subject_type')
                            ->label('Recurso Afectado'),
                        Infolists\Components\TextEntry::make('subject_id')
                            ->label('ID del Recurso'),
                        Infolists\Components\TextEntry::make('causer.name')
                            ->label('Ejecutado por')
                            ->placeholder('Sistema'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Fecha')
                            ->dateTime(),
                    ])->columns(3),

                Infolists\Components\Section::make('Cambios Registrados')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('properties.old')
                            ->label('Valores Anteriores'),
                        Infolists\Components\KeyValueEntry::make('properties.attributes')
                            ->label('Valores Nuevos'),
                    ])->columns(2),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Read Only View
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'auth' => 'info',
                        'tenant' => 'success',
                        'subscription' => 'warning',
                        'impersonation' => 'danger',
                        'settings' => 'gray',
                        'platform' => 'gray',
                        'user' => 'primary',
                        'feature-flag' => 'warning',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Recurso Afectado')
                    ->searchable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Ejecutado por')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Tipo de Log')
                    ->options([
                        'auth' => 'Autenticacion',
                        'impersonation' => 'Impersonacion',
                        'tenant' => 'Tenants',
                        'subscription' => 'Suscripciones',
                        'user' => 'Usuarios',
                        'settings' => 'Configuracion',
                        'platform' => 'Plataforma',
                        'feature-flag' => 'Feature Flags',
                        'default' => 'Otros',
                    ]),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Desde'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->label('Rango de Fechas'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for audits
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
