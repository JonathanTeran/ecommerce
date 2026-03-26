<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\TenantRegistrationResource\Pages;
use App\Models\TenantRegistration;
use App\Services\TenantProvisioningService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class TenantRegistrationResource extends Resource
{
    protected static ?string $model = TenantRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Gestión SaaS';

    protected static ?string $modelLabel = 'Solicitud de Registro';

    protected static ?string $pluralModelLabel = 'Solicitudes de Registro';

    protected static ?int $navigationSort = 0;

    public static function getNavigationBadge(): ?string
    {
        $count = TenantRegistration::pending()->verified()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos de la Tienda')
                ->schema([
                    Forms\Components\TextInput::make('store_name')->label('Nombre de la Tienda')->disabled(),
                    Forms\Components\TextInput::make('slug')->label('Slug')->disabled(),
                    Forms\Components\TextInput::make('plan.name')->label('Plan')->disabled(),
                    Forms\Components\TextInput::make('country')->label('Pais')->disabled(),
                ])->columns(2),
            Forms\Components\Section::make('Datos del Propietario')
                ->schema([
                    Forms\Components\TextInput::make('owner_name')->label('Nombre')->disabled(),
                    Forms\Components\TextInput::make('owner_email')->label('Email')->disabled(),
                    Forms\Components\TextInput::make('owner_phone')->label('Telefono')->disabled(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store_name')
                    ->label('Tienda')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('owner_name')
                    ->label('Propietario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner_email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Verificado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                    ])
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Solicitud')
                    ->modalDescription(fn (TenantRegistration $record) => "Se creara el tenant \"{$record->store_name}\" con el plan {$record->plan->name}. El propietario recibira un email con las credenciales.")
                    ->visible(fn (TenantRegistration $record) => $record->isPending() && $record->isEmailVerified())
                    ->action(function (TenantRegistration $record) {
                        $tenant = app(TenantProvisioningService::class)->provision([
                            'name' => $record->store_name,
                            'slug' => $record->slug,
                            'plan_id' => $record->plan_id,
                            'admin_name' => $record->owner_name,
                            'admin_email' => $record->owner_email,
                            'admin_password' => 'changeme123',
                            'theme_color' => 'indigo',
                            'is_demo' => true,
                            'trial_ends_at' => now()->addDays(14),
                        ]);

                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'provisioned_tenant_id' => $tenant->id,
                        ]);

                        // Send approval email
                        Mail::send('emails.tenant-registration-approved', [
                            'registration' => $record,
                            'tenant' => $tenant,
                            'loginUrl' => url('/admin/login'),
                        ], function ($message) use ($record) {
                            $message->to($record->owner_email, $record->owner_name)
                                ->subject(__('Your Store Has Been Approved!'));
                        });

                        Notification::make()
                            ->title("Tenant \"{$record->store_name}\" creado exitosamente")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (TenantRegistration $record) => $record->isPending())
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Razon del Rechazo')
                            ->required()
                            ->placeholder('Explica la razon del rechazo...'),
                    ])
                    ->action(function (TenantRegistration $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Mail::send('emails.tenant-registration-rejected', [
                            'registration' => $record,
                            'reason' => $data['rejection_reason'],
                        ], function ($message) use ($record) {
                            $message->to($record->owner_email, $record->owner_name)
                                ->subject(__('Registration Update'));
                        });

                        Notification::make()
                            ->title('Solicitud rechazada')
                            ->danger()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenantRegistrations::route('/'),
            'view' => Pages\ViewTenantRegistration::route('/{record}'),
        ];
    }
}
