<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Collection;

class TaskTimeService
{
    /**
     * Verificar y actualizar el estado de vencimiento de todas las tareas activas
     */
    public function checkAndUpdateOverdueTasks(): array
    {
        $tasks = Task::active()->get();
        $overdueCount = 0;
        $newlyOverdue = [];

        foreach ($tasks as $task) {
            $wasOverdue = $task->is_overdue;
            $task->checkAndUpdateOverdueStatus();

            if ($task->is_overdue && !$wasOverdue) {
                $overdueCount++;
                $newlyOverdue[] = $task;
            }
        }

        return [
            'total_checked' => $tasks->count(),
            'newly_overdue' => $overdueCount,
            'tasks' => $newlyOverdue,
        ];
    }

    /**
     * Notificar a los administradores sobre tareas vencidas
     */
    public function notifyAdminsAboutOverdueTasks(): int
    {
        // Obtener tareas vencidas que no han sido notificadas
        $tasks = Task::where('is_overdue', true)
            ->whereNull('overdue_notified_at')
            ->with(['board.user'])
            ->get();

        if ($tasks->isEmpty()) {
            return 0;
        }

        // Obtener todos los administradores
        $admins = User::where('tipo_usuario', 'admin')->get();

        $notificationsCreated = 0;

        foreach ($tasks as $task) {
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'task_overdue',
                    'title' => 'Tarea Vencida',
                    'message' => "La tarea '{$task->title}' ha excedido su tiempo objetivo ({$task->target_hours}h). Prioridad: {$task->priority_label}",
                    'data' => [
                        'task_id' => $task->id,
                        'board_id' => $task->board_id,
                        'title' => $task->title,
                        'priority' => $task->priority->value,
                        'hours_elapsed' => $task->hours_elapsed,
                        'target_hours' => $task->target_hours,
                        'owner' => $task->board->user->name,
                    ],
                ]);
                $notificationsCreated++;
            }

            // Marcar como notificada
            $task->markAsOverdueNotified();
        }

        return $notificationsCreated;
    }

    /**
     * Obtener estadísticas de tiempo de las tareas
     */
    public function getTimeStatistics(Collection $tasks): array
    {
        $withinTime = 0;
        $warning = 0;
        $overdue = 0;

        foreach ($tasks as $task) {
            switch ($task->time_status) {
                case 'within_time':
                    $withinTime++;
                    break;
                case 'warning':
                    $warning++;
                    break;
                case 'overdue':
                    $overdue++;
                    break;
            }
        }

        return [
            'within_time' => $withinTime,
            'warning' => $warning,
            'overdue' => $overdue,
            'total' => $tasks->count(),
        ];
    }

    /**
     * Obtener tareas críticas (próximas a vencer)
     */
    public function getCriticalTasks(): Collection
    {
        return Task::active()
            ->get()
            ->filter(function ($task) {
                return $task->time_status === 'warning' || $task->time_status === 'overdue';
            });
    }
}
