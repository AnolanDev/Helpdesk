<?php

namespace App\Enums;

enum SubTaskStatus: string
{
    case PENDIENTE = 'pendiente';
    case COMPLETADA = 'completada';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::COMPLETADA => 'Completada',
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETADA;
    }
}
