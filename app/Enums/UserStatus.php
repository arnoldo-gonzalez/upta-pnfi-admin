<?php

namespace App\Enums;

enum UserStatus: int
{
    case PENDING  = 1;
    case ACTIVE   = 2;
    case REJECTED = 3;
    case DRAFT    = 4;
    case NEEDS_CORRECTIONS = 5;

    public function label(): string
    {
        return match($this) {
            self::PENDING  => 'Pendiente',
            self::ACTIVE   => 'Activo',
            self::REJECTED => 'Rechazado',
            self::DRAFT    => 'Borrador',
            self::NEEDS_CORRECTIONS => 'Necesita correcciones',
        };
    }
}