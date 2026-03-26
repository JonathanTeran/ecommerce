<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingOnCustomer = 'waiting_on_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Abierto',
            self::InProgress => 'En Progreso',
            self::WaitingOnCustomer => 'Esperando Respuesta',
            self::Resolved => 'Resuelto',
            self::Closed => 'Cerrado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'danger',
            self::InProgress => 'warning',
            self::WaitingOnCustomer => 'info',
            self::Resolved => 'success',
            self::Closed => 'gray',
        };
    }
}
