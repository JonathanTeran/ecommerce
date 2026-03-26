<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sending = 'sending';
    case Sent = 'sent';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Scheduled => 'Programada',
            self::Sending => 'Enviando',
            self::Sent => 'Enviada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Scheduled => 'info',
            self::Sending => 'warning',
            self::Sent => 'success',
            self::Cancelled => 'danger',
        };
    }
}
