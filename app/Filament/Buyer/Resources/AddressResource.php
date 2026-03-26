<?php

namespace App\Filament\Buyer\Resources;

use App\Filament\Buyer\Resources\AddressResource\Pages;
use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Mis Direcciones';

    protected static ?string $modelLabel = 'Dirección';

    protected static ?string $pluralModelLabel = 'Direcciones';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Dirección')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'shipping' => 'Envío',
                                'billing' => 'Facturación',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('label')
                            ->label('Etiqueta')
                            ->placeholder('Ej: Casa, Oficina')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Apellido')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cedula')
                            ->label('Cédula / RUC')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('company')
                            ->label('Empresa')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20),
                    ])->columns(2),

                Forms\Components\Section::make('Ubicación')
                    ->schema([
                        Forms\Components\TextInput::make('address_line_1')
                            ->label('Dirección principal')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address_line_2')
                            ->label('Dirección secundaria')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->label('Ciudad')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('province')
                            ->label('Provincia')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Código Postal')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('country')
                            ->label('País')
                            ->default('Ecuador')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('delivery_instructions')
                            ->label('Instrucciones de entrega')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Toggle::make('is_default')
                    ->label('Dirección por defecto')
                    ->helperText('Se usará como dirección predeterminada en el checkout.'),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn (): ?int => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Etiqueta')
                    ->placeholder('Sin etiqueta')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'shipping' => 'Envío',
                        'billing' => 'Facturación',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'shipping' => 'info',
                        'billing' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('city')
                    ->label('Ciudad'),
                Tables\Columns\TextColumn::make('province')
                    ->label('Provincia'),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Predeterminada')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->defaultSort('is_default', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'shipping' => 'Envío',
                        'billing' => 'Facturación',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageAddresses::route('/'),
        ];
    }
}
