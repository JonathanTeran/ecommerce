<?php

namespace App\Enums;

enum ReturnStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ItemReceived = 'item_received';
    case Refunded = 'refunded';
    case Exchanged = 'exchanged';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Solicitada',
            self::Approved => 'Aprobada',
            self::Rejected => 'Rechazada',
            self::ItemReceived => 'Producto Recibido',
            self::Refunded => 'Reembolsada',
            self::Exchanged => 'Intercambiada',
            self::Closed => 'Cerrada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Requested => 'warning',
            self::Approved => 'info',
            self::Rejected => 'danger',
            self::ItemReceived => 'primary',
            self::Refunded => 'success',
            self::Exchanged => 'success',
            self::Closed => 'gray',
        };
    }

    /**
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Requested => [self::Approved, self::Rejected],
            self::Approved => [self::ItemReceived, self::Closed],
            self::ItemReceived => [self::Refunded, self::Exchanged],
            self::Refunded, self::Exchanged => [self::Closed],
            self::Rejected, self::Closed => [],
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions());
    }
}
