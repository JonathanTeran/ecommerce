<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Direcciones';

    public function form(Form $form): Form
    {
        return $form
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
                    ->placeholder('Casa, Oficina, etc.'),
                Forms\Components\TextInput::make('first_name')
                    ->label('Nombre')
                    ->required(),
                Forms\Components\TextInput::make('last_name')
                    ->label('Apellido')
                    ->required(),
                Forms\Components\TextInput::make('cedula')
                    ->label('Cédula / RUC'),
                Forms\Components\TextInput::make('company')
                    ->label('Empresa'),
                Forms\Components\TextInput::make('address_line_1')
                    ->label('Dirección Línea 1')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('address_line_2')
                    ->label('Dirección Línea 2')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('city')
                    ->label('Ciudad')
                    ->required(),
                Forms\Components\TextInput::make('province')
                    ->label('Provincia')
                    ->required(),
                Forms\Components\TextInput::make('postal_code')
                    ->label('Código Postal'),
                Forms\Components\TextInput::make('country')
                    ->label('País')
                    ->default('EC'),
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel(),
                Forms\Components\Textarea::make('delivery_instructions')
                    ->label('Instrucciones de Entrega')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_default')
                    ->label('Dirección por Defecto'),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'shipping' => 'info',
                        'billing' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'shipping' => 'Envío',
                        'billing' => 'Facturación',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('label')
                    ->label('Etiqueta')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('city')
                    ->label('Ciudad'),
                Tables\Columns\TextColumn::make('province')
                    ->label('Provincia'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
