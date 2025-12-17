<?php

namespace App\Jobs;

use App\Services\TaskTimeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AlertOverdueTasksJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TaskTimeService $timeService): void
    {
        Log::info('Iniciando verificación de tareas vencidas');

        // Verificar y actualizar el estado de tareas vencidas
        $result = $timeService->checkAndUpdateOverdueTasks();

        Log::info('Verificación completada', [
            'total_checked' => $result['total_checked'],
            'newly_overdue' => $result['newly_overdue'],
        ]);

        // Notificar a administradores sobre tareas vencidas
        $notificationsCreated = $timeService->notifyAdminsAboutOverdueTasks();

        Log::info('Notificaciones enviadas', [
            'count' => $notificationsCreated,
        ]);
    }
}
