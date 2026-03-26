<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\DataExportRequestResource\Pages;
use App\Models\DataExportRequest;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DataExportRequestResource extends Resource
{
    protected static ?string $model = DataExportRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $modelLabel = 'Solicitud de Datos';

    protected static ?string $pluralModelLabel = 'Solicitudes de Datos';

    protected static ?string $navigationGroup = 'Seguridad y Auditoría';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Solicitud')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Tenant')
                            ->options(Tenant::withoutGlobalScopes()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'export' => 'Exportacion de Datos',
                                'deletion' => 'Eliminacion de Datos',
                            ])
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'processing' => 'En Proceso',
                                'completed' => 'Completada',
                                'rejected' => 'Rechazada',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\Textarea::make('reason')
                            ->label('Motivo de la solicitud')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas internas del administrador')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('file_path')
                            ->label('Ruta del archivo')
                            ->visible(fn (Forms\Get $get): bool => $get('status') === 'completed'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'export' => 'info',
                        'deletion' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'export' => 'Exportacion',
                        'deletion' => 'Eliminacion',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'primary',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'processing' => 'En Proceso',
                        'completed' => 'Completada',
                        'rejected' => 'Rechazada',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Solicitado por')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completado')
                    ->dateTime()
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'export' => 'Exportacion de Datos',
                        'deletion' => 'Eliminacion de Datos',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'processing' => 'En Proceso',
                        'completed' => 'Completada',
                        'rejected' => 'Rechazada',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('completar')
                    ->label('Completar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Completar solicitud')
                    ->modalDescription('Esta accion marcara la solicitud como completada.')
                    ->visible(fn (DataExportRequest $record): bool => $record->status !== 'completed')
                    ->action(function (DataExportRequest $record): void {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Solicitud completada')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (DataExportRequest $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Motivo del rechazo')
                            ->required(),
                    ])
                    ->action(function (DataExportRequest $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'notes' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('Solicitud rechazada')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataExportRequests::route('/'),
            'create' => Pages\CreateDataExportRequest::route('/create'),
            'edit' => Pages\EditDataExportRequest::route('/{record}/edit'),
        ];
    }
}
