<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case REVIEW = 'review';
    case DONE = 'done';
    case CANCELLED = 'cancelled';
    case ARCHIVED = 'archived';

    /**
     * Obtener el label en español para mostrar
     */
    public function label(): string
    {
        return match ($this) {
            self::TODO => 'Pendiente',
            self::SCHEDULED => 'Programada',
            self::IN_PROGRESS => 'En Progreso',
            self::BLOCKED => 'Bloqueada',
            self::REVIEW => 'En Revisión',
            self::DONE => 'Completada',
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
            self::TODO => 'gray',
            self::SCHEDULED => 'purple',
            self::IN_PROGRESS => 'blue',
            self::BLOCKED => 'orange',
            self::REVIEW => 'yellow',
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
            self::TODO => 'circle',
            self::SCHEDULED => 'calendar',
            self::IN_PROGRESS => 'play-circle',
            self::BLOCKED => 'lock',
            self::REVIEW => 'eye',
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
            self::TODO,
            self::SCHEDULED,
            self::IN_PROGRESS,
            self::BLOCKED,
            self::REVIEW,
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
     * Implementa la lógica de flujo de estados
     */
    public function getAllowedTransitions(): array
    {
        return match ($this) {
            // Desde TODO se puede ir a: Scheduled, In Progress, Cancelled
            self::TODO => [
                self::SCHEDULED,
                self::IN_PROGRESS,
                self::CANCELLED,
            ],

            // Desde SCHEDULED se puede ir a: In Progress, Blocked, Cancelled, volver a TODO
            self::SCHEDULED => [
                self::TODO,
                self::IN_PROGRESS,
                self::BLOCKED,
                self::CANCELLED,
            ],

            // Desde IN_PROGRESS se puede ir a: Review, Blocked, Done, Cancelled
            self::IN_PROGRESS => [
                self::REVIEW,
                self::BLOCKED,
                self::DONE,
                self::CANCELLED,
            ],

            // Desde BLOCKED se puede desbloquear a: TODO, Scheduled, In Progress, Cancelled
            self::BLOCKED => [
                self::TODO,
                self::SCHEDULED,
                self::IN_PROGRESS,
                self::CANCELLED,
            ],

            // Desde REVIEW se puede ir a: In Progress (si requiere cambios), Done, Blocked
            self::REVIEW => [
                self::IN_PROGRESS,
                self::BLOCKED,
                self::DONE,
            ],

            // Desde DONE se puede: Archivar, reabrir (In Progress solo con confirmación)
            self::DONE => [
                self::ARCHIVED,
                self::IN_PROGRESS, // Solo con confirmación
            ],

            // Desde CANCELLED se puede: Archivar, reabrir a TODO
            self::CANCELLED => [
                self::ARCHIVED,
                self::TODO, // Solo con confirmación
            ],

            // Desde ARCHIVED normalmente no se cambia, pero se permite desarchive a su estado anterior
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
        $confirmationRequired = [
            [self::DONE, self::IN_PROGRESS], // Reabrir una tarea completada
            [self::CANCELLED, self::TODO], // Reactivar una tarea cancelada
            [self::ARCHIVED, self::TODO], // Desarchive
            [self::ARCHIVED, self::DONE], // Desarchive
            [self::ARCHIVED, self::CANCELLED], // Desarchive
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
