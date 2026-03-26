<?php

namespace App\Filament\Resources;

use App\Enums\CampaignStatus;
use App\Enums\Module;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\EmailCampaignResource\Pages;
use App\Models\EmailCampaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailCampaignResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::EmailMarketing;
    }

    protected static ?string $model = EmailCampaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $modelLabel = 'Campana de Email';

    protected static ?string $pluralModelLabel = 'Campanas de Email';

    protected static ?string $navigationGroup = 'Marketing';

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

                Forms\Components\Section::make('Configuracion de la Campana')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la Campana')
                            ->required()
                            ->placeholder('Ej: Promocion de Verano'),

                        Forms\Components\TextInput::make('subject')
                            ->label('Asunto del Email')
                            ->required()
                            ->placeholder('Ej: Descuentos de hasta 50%'),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options(collect(CampaignStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->default('draft')
                            ->required(),

                        Forms\Components\Select::make('segment')
                            ->label('Segmento')
                            ->options([
                                'all' => 'Todos los Suscriptores',
                                'customers' => 'Solo Clientes',
                                'newsletter' => 'Solo Newsletter',
                                'inactive' => 'Clientes Inactivos',
                                'vip' => 'Clientes VIP',
                            ]),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Programar Envio')
                            ->helperText('Dejar vacio para envio manual'),
                    ])->columns(2),

                Forms\Components\Section::make('Contenido del Email')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenido')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'orderedList',
                                'bulletList',
                                'h2',
                                'h3',
                                'blockquote',
                                'redo',
                                'undo',
                            ]),
                    ]),

                Forms\Components\Section::make('Estadisticas')
                    ->schema([
                        Forms\Components\TextInput::make('recipients_count')
                            ->label('Destinatarios')
                            ->disabled(),
                        Forms\Components\TextInput::make('sent_count')
                            ->label('Enviados')
                            ->disabled(),
                        Forms\Components\TextInput::make('opened_count')
                            ->label('Abiertos')
                            ->disabled(),
                        Forms\Components\TextInput::make('clicked_count')
                            ->label('Clicks')
                            ->disabled(),
                        Forms\Components\TextInput::make('bounced_count')
                            ->label('Rebotados')
                            ->disabled(),
                        Forms\Components\TextInput::make('unsubscribed_count')
                            ->label('Desuscritos')
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->visibleOn('edit'),
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
                    ->label('Campana')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (CampaignStatus $state) => $state->label())
                    ->color(fn (CampaignStatus $state) => $state->color()),

                Tables\Columns\TextColumn::make('segment')
                    ->label('Segmento')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'all' => 'Todos',
                        'customers' => 'Clientes',
                        'newsletter' => 'Newsletter',
                        'inactive' => 'Inactivos',
                        'vip' => 'VIP',
                        default => $state ?? '-',
                    }),

                Tables\Columns\TextColumn::make('sent_count')
                    ->label('Enviados')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('opened_count')
                    ->label('Abiertos')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Programado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Enviado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(collect(CampaignStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-m-document-duplicate')
                    ->action(function (EmailCampaign $record) {
                        $record->replicate(['sent_count', 'opened_count', 'clicked_count', 'bounced_count', 'unsubscribed_count', 'recipients_count', 'sent_at', 'scheduled_at'])
                            ->fill(['status' => 'draft', 'name' => $record->name.' (Copia)'])
                            ->save();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailCampaigns::route('/'),
            'create' => Pages\CreateEmailCampaign::route('/create'),
            'edit' => Pages\EditEmailCampaign::route('/{record}/edit'),
        ];
    }
}
