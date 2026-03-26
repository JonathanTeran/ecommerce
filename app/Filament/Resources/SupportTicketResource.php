<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupportTicketResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Support;
    }

    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $modelLabel = 'Ticket de Soporte';

    protected static ?string $pluralModelLabel = 'Tickets de Soporte';

    protected static ?string $navigationGroup = 'Soporte';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', [TicketStatus::Open->value, TicketStatus::InProgress->value])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

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

                Forms\Components\Section::make('Informacion del Ticket')
                    ->schema([
                        Forms\Components\TextInput::make('ticket_number')
                            ->label('Numero de Ticket')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Cliente')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->label('Pedido Relacionado')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('subject')
                            ->label('Asunto')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options(collect(TicketStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->required()
                            ->default('open'),

                        Forms\Components\Select::make('priority')
                            ->label('Prioridad')
                            ->options(collect(TicketPriority::cases())->mapWithKeys(fn ($p) => [$p->value => $p->label()]))
                            ->required()
                            ->default('medium'),

                        Forms\Components\Select::make('category')
                            ->label('Categoria')
                            ->options([
                                'general' => 'Consulta General',
                                'order' => 'Problema con Pedido',
                                'product' => 'Consulta de Producto',
                                'payment' => 'Problema de Pago',
                                'shipping' => 'Problema de Envio',
                                'return' => 'Devolucion',
                                'account' => 'Cuenta',
                                'other' => 'Otro',
                            ]),

                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedAgent', 'name')
                            ->label('Asignado a')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Historial de Mensajes')
                    ->schema([
                        Forms\Components\Repeater::make('messages')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Usuario')
                                    ->required()
                                    ->default(fn () => auth()->id()),

                                Forms\Components\Toggle::make('is_from_admin')
                                    ->label('Respuesta Admin')
                                    ->default(true),

                                Forms\Components\Textarea::make('message')
                                    ->label('Mensaje')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar Respuesta')
                            ->collapsible(),
                    ])
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

                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('# Ticket')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (TicketStatus $state) => $state->label())
                    ->color(fn (TicketStatus $state) => $state->color()),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge()
                    ->formatStateUsing(fn (TicketPriority $state) => $state->label())
                    ->color(fn (TicketPriority $state) => $state->color()),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'general' => 'General',
                        'order' => 'Pedido',
                        'product' => 'Producto',
                        'payment' => 'Pago',
                        'shipping' => 'Envio',
                        'return' => 'Devolucion',
                        'account' => 'Cuenta',
                        'other' => 'Otro',
                        default => $state ?? '-',
                    }),

                Tables\Columns\TextColumn::make('assignedAgent.name')
                    ->label('Asignado')
                    ->default('Sin asignar'),

                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Mensajes')
                    ->counts('messages')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(collect(TicketStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(collect(TicketPriority::cases())->mapWithKeys(fn ($p) => [$p->value => $p->label()])),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Asignado a')
                    ->relationship('assignedAgent', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('resolve')
                    ->label('Resolver')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (SupportTicket $record) => in_array($record->status, [TicketStatus::Open, TicketStatus::InProgress, TicketStatus::WaitingOnCustomer]))
                    ->requiresConfirmation()
                    ->action(fn (SupportTicket $record) => $record->update([
                        'status' => TicketStatus::Resolved,
                        'resolved_at' => now(),
                    ])),
                Tables\Actions\Action::make('close')
                    ->label('Cerrar')
                    ->icon('heroicon-m-x-circle')
                    ->color('gray')
                    ->visible(fn (SupportTicket $record) => $record->status === TicketStatus::Resolved)
                    ->action(fn (SupportTicket $record) => $record->update([
                        'status' => TicketStatus::Closed,
                        'closed_at' => now(),
                    ])),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'create' => Pages\CreateSupportTicket::route('/create'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
