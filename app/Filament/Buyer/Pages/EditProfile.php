<?php

namespace App\Filament\Buyer\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->description('Update your personal details.')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                    ])->columns(2),

                Section::make('Identificación y Facturación')
                    ->description('Datos necesarios para sus facturas.')
                    ->schema([
                        TextInput::make('identification_number')
                            ->label('Cédula / RUC')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('billing_city')
                            ->label('Ciudad')
                            ->maxLength(255),
                        TextInput::make('billing_address')
                            ->label('Dirección')
                            ->columnSpanFull()
                            ->maxLength(255),
                    ])->columns(2),

                $this->getPasswordFormComponent(),
            ]);
    }
}
