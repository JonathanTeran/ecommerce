<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case ON_HOLD = 'on_hold';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::CONFIRMED => 'Confirmado',
            self::PROCESSING => 'En Proceso',
            self::SHIPPED => 'Enviado',
            self::DELIVERED => 'Entregado',
            self::CANCELLED => 'Cancelado',
            self::REFUNDED => 'Reembolsado',
            self::ON_HOLD => 'En Espera',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'info',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'gray',
            self::ON_HOLD => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::CONFIRMED => 'heroicon-o-check-circle',
            self::PROCESSING => 'heroicon-o-cog',
            self::SHIPPED => 'heroicon-o-truck',
            self::DELIVERED => 'heroicon-o-check-badge',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::REFUNDED => 'heroicon-o-arrow-uturn-left',
            self::ON_HOLD => 'heroicon-o-pause-circle',
        };
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        $transitions = [
            self::PENDING->value => [self::CONFIRMED, self::CANCELLED, self::ON_HOLD],
            self::CONFIRMED->value => [self::PROCESSING, self::CANCELLED, self::ON_HOLD],
            self::PROCESSING->value => [self::SHIPPED, self::CANCELLED, self::ON_HOLD],
            self::SHIPPED->value => [self::DELIVERED, self::CANCELLED],
            self::DELIVERED->value => [self::REFUNDED],
            self::ON_HOLD->value => [self::PENDING, self::CONFIRMED, self::PROCESSING, self::CANCELLED],
            self::CANCELLED->value => [],
            self::REFUNDED->value => [],
        ];

        return in_array($newStatus, $transitions[$this->value] ?? []);
    }

    public static function activeStatuses(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::PROCESSING,
            self::SHIPPED,
            self::ON_HOLD,
        ];
    }
}
