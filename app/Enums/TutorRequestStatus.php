<?php

namespace App\Enums;

enum TutorRequestStatus: int
{
    case NO_REQUEST = 1;
    case PENDING    = 2;
    case ACCEPTED   = 3;
    case REJECTED   = 4;

    public function label(): string
    {
        return match($this) {
            self::NO_REQUEST => 'Sin Solicitud',
            self::PENDING    => 'Pendiente',
            self::ACCEPTED   => 'Activo',
            self::REJECTED => 'Rechazado'
        };
    }
}
