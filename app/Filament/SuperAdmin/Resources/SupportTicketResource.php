<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Filament\SuperAdmin\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Soporte';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Ticket de Soporte';

    protected static ?string $pluralModelLabel = 'Tickets de Soporte';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['tenant', 'user', 'assignedAgent']))
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('# Ticket')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

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

                Tables\Columns\TextColumn::make('assignedAgent.name')
                    ->label('Asignado')
                    ->default('Sin asignar'),

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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informacion del Ticket')
                    ->schema([
                        Infolists\Components\TextEntry::make('ticket_number')
                            ->label('# Ticket')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('tenant.name')
                            ->label('Tenant')
                            ->badge()
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('subject')
                            ->label('Asunto')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Cliente'),
                        Infolists\Components\TextEntry::make('assignedAgent.name')
                            ->label('Asignado a')
                            ->default('Sin asignar'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (TicketStatus $state) => $state->label())
                            ->color(fn (TicketStatus $state) => $state->color()),
                        Infolists\Components\TextEntry::make('priority')
                            ->label('Prioridad')
                            ->badge()
                            ->formatStateUsing(fn (TicketPriority $state) => $state->label())
                            ->color(fn (TicketPriority $state) => $state->color()),
                        Infolists\Components\TextEntry::make('category')
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
                        Infolists\Components\TextEntry::make('order.order_number')
                            ->label('Pedido Relacionado')
                            ->default('-'),
                    ])->columns(3),

                Infolists\Components\Section::make('Fechas')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('resolved_at')
                            ->label('Resuelto')
                            ->dateTime('d/m/Y H:i')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('closed_at')
                            ->label('Cerrado')
                            ->dateTime('d/m/Y H:i')
                            ->default('-'),
                    ])->columns(3),

                Infolists\Components\Section::make('Mensajes')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('messages')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Usuario'),
                                Infolists\Components\IconEntry::make('is_from_admin')
                                    ->label('Admin')
                                    ->boolean(),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha')
                                    ->dateTime('d/m/Y H:i'),
                                Infolists\Components\TextEntry::make('message')
                                    ->label('Mensaje')
                                    ->columnSpanFull(),
                            ])->columns(3),
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
            'view' => Pages\ViewSupportTicket::route('/{record}'),
        ];
    }
}
