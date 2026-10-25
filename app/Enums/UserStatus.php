<?php

namespace App\Enums;

enum UserStatus: int
{
    case PENDING  = 1;
    case ACTIVE   = 2;
    case REJECTED = 3;

    public function label(): string
    {
        return match($this) {
            self::PENDING  => 'Pendiente',
            self::ACTIVE   => 'Activo',
            self::REJECTED => 'Rechazado',
        };
    }
}