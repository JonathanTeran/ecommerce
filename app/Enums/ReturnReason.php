<?php

namespace App\Enums;

enum ReturnReason: string
{
    case Defective = 'defective';
    case WrongItem = 'wrong_item';
    case NotAsDescribed = 'not_as_described';
    case ChangedMind = 'changed_mind';
    case Damaged = 'damaged';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Defective => 'Producto Defectuoso',
            self::WrongItem => 'Producto Incorrecto',
            self::NotAsDescribed => 'No Coincide con Descripcion',
            self::Damaged => 'Producto Danado',
            self::ChangedMind => 'Cambio de Opinion',
            self::Other => 'Otro',
        };
    }
}
