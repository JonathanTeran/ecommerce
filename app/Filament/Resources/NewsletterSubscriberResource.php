<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $modelLabel = 'Suscriptor';

    protected static ?string $pluralModelLabel = 'Suscriptores Newsletter';

    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Fuente')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscribed_at')
                    ->label('Suscrito')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unsubscribed_at')
                    ->label('Dado de baja')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->placeholder('Todos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export_emails')
                        ->label('Exportar emails')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): \Symfony\Component\HttpFoundation\StreamedResponse {
                            return response()->streamDownload(function () use ($records) {
                                $csv = fopen('php://output', 'w');
                                fputcsv($csv, ['Email', 'Nombre', 'Fuente', 'Suscrito']);

                                foreach ($records as $record) {
                                    fputcsv($csv, [
                                        $record->email,
                                        $record->name,
                                        $record->source,
                                        $record->subscribed_at?->toDateTimeString(),
                                    ]);
                                }

                                fclose($csv);
                            }, 'newsletter-subscribers.csv', [
                                'Content-Type' => 'text/csv',
                            ]);
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListNewsletterSubscribers::route('/'),
            'create' => Pages\CreateNewsletterSubscriber::route('/create'),
            'edit' => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }
}
