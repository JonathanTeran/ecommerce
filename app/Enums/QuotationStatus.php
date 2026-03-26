<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum QuotationStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Converted = 'converted';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Pending => 'Pendiente',
            self::Approved => 'Aprobada',
            self::Rejected => 'Rechazada',
            self::Expired => 'Expirada',
            self::Converted => 'Convertida a Pedido',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Expired => 'gray',
            self::Converted => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::Pending => 'heroicon-o-clock',
            self::Approved => 'heroicon-o-check-circle',
            self::Rejected => 'heroicon-o-x-circle',
            self::Expired => 'heroicon-o-calendar',
            self::Converted => 'heroicon-o-arrow-path',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        $transitions = [
            self::Draft->value => [self::Pending],
            self::Pending->value => [self::Approved, self::Rejected, self::Expired],
            self::Approved->value => [self::Converted, self::Expired],
            self::Rejected->value => [],
            self::Expired->value => [],
            self::Converted->value => [],
        ];

        return in_array($newStatus, $transitions[$this->value] ?? []);
    }
}
