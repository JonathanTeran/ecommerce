<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasIcon, HasLabel
{
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case PAYPHONE = 'payphone';
    case PAYPAL = 'paypal';
    case KUSHKI = 'kushki';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH_ON_DELIVERY = 'cash_on_delivery';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CREDIT_CARD => 'Tarjeta de Crédito',
            self::DEBIT_CARD => 'Tarjeta de Débito',
            self::PAYPHONE => 'PayPhone',
            self::PAYPAL => 'PayPal',
            self::KUSHKI => 'Kushki / Datafast',
            self::BANK_TRANSFER => 'Transferencia Bancaria',
            self::CASH_ON_DELIVERY => 'Pago Contra Entrega',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CREDIT_CARD => 'heroicon-o-credit-card',
            self::DEBIT_CARD => 'heroicon-o-credit-card',
            self::PAYPHONE => 'heroicon-o-device-phone-mobile',
            self::PAYPAL => 'heroicon-o-globe-alt',
            self::KUSHKI => 'heroicon-o-credit-card',
            self::BANK_TRANSFER => 'heroicon-o-building-library',
            self::CASH_ON_DELIVERY => 'heroicon-o-banknotes',
        };
    }

    public function requiresOnlineProcessing(): bool
    {
        return in_array($this, [
            self::CREDIT_CARD,
            self::DEBIT_CARD,
            self::PAYPHONE,
            self::PAYPAL,
            self::KUSHKI,
        ]);
    }

    public function fee(): float
    {
        return match ($this) {
            self::CREDIT_CARD => 0.035,      // 3.5%
            self::DEBIT_CARD => 0.025,       // 2.5%
            self::PAYPHONE => 0.02,          // 2%
            self::PAYPAL => 0.045,           // 4.5%
            self::KUSHKI => 0.035,           // 3.5%
            self::BANK_TRANSFER => 0,        // 0%
            self::CASH_ON_DELIVERY => 0.01,  // 1%
        };
    }
}
