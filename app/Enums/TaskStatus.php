<?php

namespace App\Enums;

enum TaskStatus: string
{
    case RECEIVED = 'received';
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case DONE = 'done';
    case CANCELLED = 'cancelled';
    case ARCHIVED = 'archived';

    /**
     * Obtener el label en español para mostrar
     */
    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => 'Recibida',
            self::TODO => 'Por Hacer',
            self::IN_PROGRESS => 'En Progreso',
            self::BLOCKED => 'Bloqueada',
            self::DONE => 'Finalizada',
            self::CANCELLED => 'Cancelada',
            self::ARCHIVED => 'Archivada',
        };
    }

    /**
     * Obtener el color del estado para la UI
     */
    public function color(): string
    {
        return match ($this) {
            self::RECEIVED => 'indigo',
            self::TODO => 'gray',
            self::IN_PROGRESS => 'blue',
            self::BLOCKED => 'orange',
            self::DONE => 'green',
            self::CANCELLED => 'red',
            self::ARCHIVED => 'slate',
        };
    }

    /**
     * Obtener el ícono del estado
     */
    public function icon(): string
    {
        return match ($this) {
            self::RECEIVED => 'inbox',
            self::TODO => 'clipboard-list',
            self::IN_PROGRESS => 'play-circle',
            self::BLOCKED => 'lock',
            self::DONE => 'check-circle',
            self::CANCELLED => 'x-circle',
            self::ARCHIVED => 'archive',
        };
    }

    /**
     * Verificar si el estado es activo (no terminado)
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::RECEIVED,
            self::TODO,
            self::IN_PROGRESS,
            self::BLOCKED,
        ]);
    }

    /**
     * Verificar si el estado es final (no se puede modificar la tarea fácilmente)
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::DONE,
            self::CANCELLED,
            self::ARCHIVED,
        ]);
    }

    /**
     * Obtener las transiciones válidas desde este estado
     *
     * Flujo ideal:
     * Received → To Do → In Progress → Done → Archived
     *
     * Blocked es un estado lateral (temporal) que puede aplicarse desde cualquier estado activo
     * Cancelled puede aplicarse desde cualquier estado activo
     */
    public function getAllowedTransitions(): array
    {
        return match ($this) {
            // RECEIVED: Tarea recién llegada
            // Puede ir a: To Do (cuando se entiende y planifica), Cancelled (si se rechaza)
            self::RECEIVED => [
                self::TODO,
                self::CANCELLED,
            ],

            // TO DO: Tarea entendida y lista para ejecutarse
            // Puede ir a: In Progress (cuando se empieza), Cancelled
            self::TODO => [
                self::IN_PROGRESS,
                self::CANCELLED,
            ],

            // IN_PROGRESS: Tarea siendo trabajada activamente
            // Puede ir a: Blocked (si hay impedimento), Done (al finalizar), Cancelled
            self::IN_PROGRESS => [
                self::BLOCKED,
                self::DONE,
                self::CANCELLED,
            ],

            // BLOCKED: Estado temporal cuando algo externo impide avanzar
            // Puede volver a: To Do (replantear), In Progress (continuar)
            self::BLOCKED => [
                self::TODO,
                self::IN_PROGRESS,
                self::CANCELLED,
            ],

            // DONE: Tarea completada al 100%
            // Puede ir a: Archived (archivar), In Progress (reabrir con confirmación)
            self::DONE => [
                self::ARCHIVED,
                self::IN_PROGRESS, // Requiere confirmación
            ],

            // CANCELLED: Tarea cancelada
            // Puede ir a: Archived (archivar), To Do (reactivar con confirmación)
            self::CANCELLED => [
                self::ARCHIVED,
                self::TODO, // Requiere confirmación
            ],

            // ARCHIVED: Tarea archivada
            // Permite desarchive a estados específicos (todos requieren confirmación)
            self::ARCHIVED => [
                self::TODO,
                self::DONE,
                self::CANCELLED,
            ],
        };
    }

    /**
     * Verificar si se puede transicionar a otro estado
     */
    public function canTransitionTo(TaskStatus $newStatus): bool
    {
        // Un estado siempre puede quedarse en sí mismo
        if ($this === $newStatus) {
            return true;
        }

        return in_array($newStatus, $this->getAllowedTransitions());
    }

    /**
     * Verificar si la transición requiere confirmación del usuario
     */
    public function requiresConfirmation(TaskStatus $newStatus): bool
    {
        // Transiciones que requieren confirmación explícita
        // (revertir acciones finales o desarchive)
        $confirmationRequired = [
            [self::DONE, self::IN_PROGRESS],      // Reabrir una tarea completada
            [self::CANCELLED, self::TODO],        // Reactivar una tarea cancelada
            [self::ARCHIVED, self::TODO],         // Desarchive a To Do
            [self::ARCHIVED, self::DONE],         // Desarchive a Done
            [self::ARCHIVED, self::CANCELLED],    // Desarchive a Cancelled
        ];

        foreach ($confirmationRequired as [$from, $to]) {
            if ($this === $from && $newStatus === $to) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtener todos los estados como array (para selects, etc.)
     */
    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $status) {
            $array[$status->value] = $status->label();
        }
        return $array;
    }

    /**
     * Obtener estados activos
     */
    public static function activeStatuses(): array
    {
        return array_filter(self::cases(), fn($status) => $status->isActive());
    }

    /**
     * Obtener estados finales
     */
    public static function finalStatuses(): array
    {
        return array_filter(self::cases(), fn($status) => $status->isFinal());
    }
}
