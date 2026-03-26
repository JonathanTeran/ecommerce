<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\SuperAdminUserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'Usuario Administrador';

    protected static ?string $navigationGroup = 'Seguridad y Auditoría';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        /** @var User $user */
        $user = auth()->user();
        return $user && $user->isSuperAdmin() && in_array($user->sub_role, ['owner', null]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('roles', function (Builder $query) {
            $query->where('name', 'super_admin');
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Base')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Corporativo')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Permisos y Accesos')
                    ->schema([
                        Forms\Components\Select::make('sub_role')
                            ->label('Sub-rol de Plataforma')
                            ->options([
                                'owner' => 'Platform Owner',
                                'support' => 'Support Admin',
                                'billing' => 'Billing Admin',
                                'security' => 'Security Admin',
                                'compliance' => 'Compliance Admin',
                            ])
                            ->helperText('Determina qué puede hacer el administrador dentro del Panel SuperAdmin. (Implementación base)')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Cuenta Activa')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre Completo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sub_role')
                    ->label('Cargo/Rol')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Último Acceso')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(User $record) => $record->id !== auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuperAdminUsers::route('/'),
            'create' => Pages\CreateSuperAdminUser::route('/create'),
            'edit' => Pages\EditSuperAdminUser::route('/{record}/edit'),
        ];
    }
}
