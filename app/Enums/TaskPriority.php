<?php

namespace App\Enums;

enum TaskPriority: string
{
    case BAJA = 'baja';
    case NORMAL = 'normal';
    case ALTA = 'alta';
    case URGENTE = 'urgente';

    public function label(): string
    {
        return match ($this) {
            self::BAJA => 'Baja',
            self::NORMAL => 'Normal',
            self::ALTA => 'Alta',
            self::URGENTE => 'Urgente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BAJA => 'gray',
            self::NORMAL => 'blue',
            self::ALTA => 'orange',
            self::URGENTE => 'red',
        };
    }

    /**
     * Tiempo objetivo en horas según la prioridad
     */
    public function targetHours(): float
    {
        return match ($this) {
            self::URGENTE => 0, // Acción inmediata
            self::ALTA => 1,
            self::NORMAL => 6,
            self::BAJA => 48,
        };
    }

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $priority) {
            $array[$priority->value] = $priority->label();
        }
        return $array;
    }
}
