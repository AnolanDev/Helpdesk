<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTaskDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-due-reminders {--hours=24 : Hours before due date to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for tasks that are due soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $this->info("Buscando tareas que vencen en las próximas {$hours} horas...");

        // Obtener tareas activas que vencen en las próximas X horas
        $now = Carbon::now();
        $threshold = $now->copy()->addHours($hours);

        $tasks = Task::active()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$now, $threshold])
            ->with(['assignedUser', 'creator'])
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No hay tareas próximas a vencer.');
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$tasks->count()} tareas próximas a vencer.");

        $sentCount = 0;

        foreach ($tasks as $task) {
            // Calcular horas restantes
            $hoursRemaining = $now->diffInHours($task->due_date, false);

            if ($hoursRemaining < 0) {
                $hoursRemaining = 0;
            }

            // Notificar al usuario asignado
            if ($task->assigned_to) {
                $this->sendReminder($task, $task->assignedUser, $hoursRemaining);
                $sentCount++;
            }

            // Notificar también al creador si no es el mismo que el asignado
            if ($task->created_by && $task->created_by !== $task->assigned_to) {
                $this->sendReminder($task, $task->creator, $hoursRemaining);
                $sentCount++;
            }

            $this->line("  - {$task->task_number}: {$task->title}");
        }

        $this->info("✓ Se enviaron {$sentCount} notificaciones.");

        return Command::SUCCESS;
    }

    /**
     * Send reminder notification
     */
    protected function sendReminder(Task $task, $user, $hoursRemaining)
    {
        if (!$user) {
            return;
        }

        // Verificar si ya se envió una notificación reciente (últimas 12 horas)
        $recentNotification = Notification::where('user_id', $user->id)
            ->where('type', 'task_due_reminder')
            ->where('created_at', '>', Carbon::now()->subHours(12))
            ->whereJsonContains('data->task_id', $task->id)
            ->exists();

        if ($recentNotification) {
            return; // Ya se envió una notificación reciente
        }

        $message = $hoursRemaining > 0
            ? "La tarea '{$task->title}' vence en " . round($hoursRemaining) . " horas"
            : "La tarea '{$task->title}' está vencida";

        Notification::create([
            'user_id' => $user->id,
            'type' => 'task_due_reminder',
            'title' => 'Recordatorio de tarea',
            'message' => $message,
            'data' => [
                'task_id' => $task->id,
                'task_number' => $task->task_number,
                'due_date' => $task->due_date->toIso8601String(),
                'hours_remaining' => round($hoursRemaining, 1),
            ],
        ]);
    }
}
