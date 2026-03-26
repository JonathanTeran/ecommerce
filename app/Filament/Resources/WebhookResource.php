<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Enums\WebhookEvent;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\WebhookResource\Pages;
use App\Filament\Resources\WebhookResource\RelationManagers;
use App\Models\Webhook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebhookResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Webhooks;
    }

    protected static ?string $model = Webhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $modelLabel = 'Webhook';

    protected static ?string $pluralModelLabel = 'Webhooks';

    protected static ?string $navigationGroup = 'Configuración';

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
                Forms\Components\Section::make('Configuración del Webhook')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com/webhook'),
                        Forms\Components\TextInput::make('secret')
                            ->label('Secreto')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('Se usa para firmar los payloads con HMAC SHA-256'),
                        Forms\Components\TextInput::make('description')
                            ->label('Descripción')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
                Forms\Components\Section::make('Eventos')
                    ->schema([
                        Forms\Components\CheckboxList::make('events')
                            ->label('Eventos suscritos')
                            ->options(
                                collect(WebhookEvent::cases())
                                    ->mapWithKeys(fn (WebhookEvent $event) => [$event->value => $event->label()])
                                    ->toArray()
                            )
                            ->required()
                            ->columns(2),
                    ]),
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
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('events')
                    ->label('Eventos')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => is_array($state) ? count($state).' eventos' : '0 eventos'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('deliveries_count')
                    ->label('Entregas')
                    ->counts('deliveries')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\DeliveriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhooks::route('/'),
            'create' => Pages\CreateWebhook::route('/create'),
            'edit' => Pages\EditWebhook::route('/{record}/edit'),
        ];
    }
}
