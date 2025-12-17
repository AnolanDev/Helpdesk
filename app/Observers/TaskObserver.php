<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\TaskTimeService;

class TaskObserver
{
    protected TaskTimeService $timeService;

    public function __construct(TaskTimeService $timeService)
    {
        $this->timeService = $timeService;
    }

    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        // Verificar si ya está vencida al crearse (caso de prioridad URGENTE)
        $task->checkAndUpdateOverdueStatus();
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        // Si cambió la prioridad, recalcular estado de vencimiento
        if ($task->wasChanged('priority')) {
            $task->checkAndUpdateOverdueStatus();
        }
    }

    /**
     * Handle the Task "saving" event.
     */
    public function saving(Task $task): void
    {
        // Verificar estado de vencimiento antes de guardar
        if (!$task->exists || $task->isDirty('priority')) {
            // No podemos llamar a checkAndUpdateOverdueStatus aquí porque causaría un loop
            // La verificación se hará en created o updated
        }
    }
}
