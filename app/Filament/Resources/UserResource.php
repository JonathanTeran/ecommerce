<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Orders;
    }
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $navigationGroup = 'Gestión de Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->label('Tenant')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Email Verificado En'),
                Forms\Components\TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel(),
                Forms\Components\Select::make('identification_type')
                    ->label('Tipo Identificación')
                    ->options([
                        'cedula' => 'Cédula',
                        'ruc' => 'RUC',
                        'passport' => 'Pasaporte',
                    ])
                    ->default('cedula')
                    ->required(),
                Forms\Components\TextInput::make('identification_number')
                    ->label('Número Identificación')
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                Forms\Components\DatePicker::make('date_of_birth')
                    ->label('Fecha de Nacimiento'),
                Forms\Components\TextInput::make('gender')
                    ->label('Género'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->required(),
                Forms\Components\DateTimePicker::make('last_login_at')
                    ->label('Último Acceso'),
                Forms\Components\TextInput::make('last_login_ip')
                    ->label('IP Último Acceso'),
                Forms\Components\Textarea::make('preferences')
                    ->label('Preferencias')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('newsletter_subscribed')
                    ->label('Suscrito al Boletín')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('identification_type')
                    ->label('Tipo ID')
                    ->badge()
                    ->colors([
                        'primary' => 'cedula',
                        'success' => 'ruc',
                        'warning' => 'passport',
                    ]),
                Tables\Columns\TextColumn::make('identification_number')
                    ->label('Num. Identificación')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Cumpleaños')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Género')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Último Acceso')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_login_ip')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\IconColumn::make('newsletter_subscribed')
                    ->label('Boletín')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && ! $user->isSuperAdmin()) {
            $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            }
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
