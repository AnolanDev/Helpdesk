<?php

namespace App\Enums;

enum TaskStatus: string
{
    case CREADA = 'creada';
    case ANALIZADA = 'analizada';
    case PROGRAMADA = 'programada';
    case EN_PROGRESO = 'en_progreso';
    case FINALIZADA = 'finalizada';
    case CANCELADA = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::CREADA => 'Creada',
            self::ANALIZADA => 'Analizada',
            self::PROGRAMADA => 'Programada',
            self::EN_PROGRESO => 'En Progreso',
            self::FINALIZADA => 'Finalizada',
            self::CANCELADA => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CREADA => 'gray',
            self::ANALIZADA => 'blue',
            self::PROGRAMADA => 'purple',
            self::EN_PROGRESO => 'yellow',
            self::FINALIZADA => 'green',
            self::CANCELADA => 'red',
        };
    }

    public function canTransitionTo(TaskStatus $newStatus): bool
    {
        // No se puede cambiar desde estados finales
        if ($this === self::FINALIZADA || $this === self::CANCELADA) {
            return false;
        }

        // Desde CREADA puede ir a ANALIZADA o CANCELADA
        if ($this === self::CREADA) {
            return in_array($newStatus, [self::ANALIZADA, self::CANCELADA]);
        }

        // Desde ANALIZADA puede ir a PROGRAMADA o CANCELADA
        if ($this === self::ANALIZADA) {
            return in_array($newStatus, [self::PROGRAMADA, self::CANCELADA]);
        }

        // Desde PROGRAMADA puede ir a EN_PROGRESO o CANCELADA
        if ($this === self::PROGRAMADA) {
            return in_array($newStatus, [self::EN_PROGRESO, self::CANCELADA]);
        }

        // Desde EN_PROGRESO puede ir a FINALIZADA o CANCELADA
        if ($this === self::EN_PROGRESO) {
            return in_array($newStatus, [self::FINALIZADA, self::CANCELADA]);
        }

        return false;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::FINALIZADA, self::CANCELADA]);
    }

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $status) {
            $array[$status->value] = $status->label();
        }
        return $array;
    }
}
