<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Models\Task;
use App\Models\Board;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Calcular estadísticas según el rol del usuario
        $statsQuery = Ticket::query();

        if ($user->isUsuarioFinal()) {
            // Usuario final: solo sus propios tickets
            $statsQuery->where('user_id', $user->id);
        } elseif ($user->isTech()) {
            // Técnico: tickets creados por él o asignados a él
            $statsQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }
        // Admin: ve todos los tickets (no aplica filtro)

        $stats = [
            'open_tickets' => (clone $statsQuery)->open()->count(),
            'urgent_tickets' => (clone $statsQuery)
                ->open()
                ->where('priority', Ticket::PRIORITY_URGENT)
                ->count(),
            'resolved_this_month' => (clone $statsQuery)
                ->where('status', Ticket::STATUS_RESOLVED)
                ->whereMonth('resolved_at', now()->month)
                ->whereYear('resolved_at', now()->year)
                ->count(),
            'total_users' => $user->isAdmin() ? User::active()->count() : null,
        ];

        // Actividad reciente (últimas 5 actividades)
        $recentActivitiesQuery = TicketActivity::with(['ticket'])
            ->orderBy('created_at', 'desc')
            ->limit(10);

        if ($user->isUsuarioFinal()) {
            // Usuario final: solo actividad de sus tickets
            $recentActivitiesQuery->whereHas('ticket', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->isTech()) {
            // Técnico: actividad de tickets que creó o le asignaron
            $recentActivitiesQuery->whereHas('ticket', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }
        // Admin: ve toda la actividad

        $recentActivities = $recentActivitiesQuery->get();

        // Permisos del usuario
        $permissions = [
            'can_view_users' => $user->isAdmin(),
            'can_create_tickets' => true, // Todos pueden crear tickets
            'can_view_reports' => $user->isAdmin() || $user->isTech(),
        ];

        // Métricas de Tareas
        $tasksQuery = Task::query();

        if ($user->isAdmin()) {
            // Admin: ve tareas de sus tableros + tableros de usuarios tech
            $tasksQuery->whereHas('board', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', function ($q) {
                      $q->where('tipo_usuario', 'tech');
                  });
            });
        } else {
            // Usuarios normales/tech: solo tareas de sus tableros
            $tasksQuery->whereHas('board', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $totalTasks = (clone $tasksQuery)->count();
        $completedTasks = (clone $tasksQuery)->where('status', 'finalizada')->count();
        $canceledTasks = (clone $tasksQuery)->where('status', 'cancelada')->count();
        $overdueTasks = (clone $tasksQuery)->where('is_overdue', true)->count();

        // Tareas completadas en los últimos 7 días
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = (clone $tasksQuery)
                ->where('status', 'finalizada')
                ->whereDate('completed_at', $date->toDateString())
                ->count();

            $last7Days[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D d'),
                'count' => $count
            ];
        }

        // Análisis de eficiencia basado en tiempo objetivo vs tiempo real
        $completedTasksWithTime = (clone $tasksQuery)
            ->where('status', 'finalizada')
            ->whereNotNull('completed_at')
            ->get();

        $avgCompletionTime = 0;
        $efficientTasks = 0; // Completadas ANTES del tiempo objetivo
        $onTimeTasks = 0; // Completadas DENTRO del tiempo objetivo
        $delayedTasks = 0; // Completadas DESPUÉS del tiempo objetivo
        $totalEfficiencyPercentage = 0;
        $avgTimeVariance = 0; // Diferencia promedio entre tiempo real y objetivo

        if ($completedTasksWithTime->count() > 0) {
            $totalHours = 0;
            $totalVariance = 0;

            foreach ($completedTasksWithTime as $task) {
                $hoursSpent = $task->created_at->diffInHours($task->completed_at, true);
                $targetHours = $task->target_hours;
                $totalHours += $hoursSpent;

                // Calcular varianza (negativo = terminó antes, positivo = se retrasó)
                $variance = $hoursSpent - $targetHours;
                $totalVariance += $variance;

                // Clasificar según eficiencia
                if ($hoursSpent < $targetHours * 0.8) {
                    // Terminó usando menos del 80% del tiempo objetivo = Eficiente
                    $efficientTasks++;
                } elseif ($hoursSpent <= $targetHours) {
                    // Terminó dentro del tiempo objetivo = A tiempo
                    $onTimeTasks++;
                } else {
                    // Excedió el tiempo objetivo = Con retraso
                    $delayedTasks++;
                }

                // Calcular porcentaje de eficiencia (menor a 100% = eficiente, mayor a 100% = con retraso)
                if ($targetHours > 0) {
                    $totalEfficiencyPercentage += ($hoursSpent / $targetHours) * 100;
                }
            }

            $avgCompletionTime = round($totalHours / $completedTasksWithTime->count(), 1);
            $avgTimeVariance = round($totalVariance / $completedTasksWithTime->count(), 1);
            $avgEfficiency = round($totalEfficiencyPercentage / $completedTasksWithTime->count(), 1);
        } else {
            $avgEfficiency = 0;
        }

        // Distribución por estado
        $tasksByStatus = [
            'creada' => (clone $tasksQuery)->where('status', 'creada')->count(),
            'analizada' => (clone $tasksQuery)->where('status', 'analizada')->count(),
            'programada' => (clone $tasksQuery)->where('status', 'programada')->count(),
            'en_progreso' => (clone $tasksQuery)->where('status', 'en_progreso')->count(),
            'finalizada' => $completedTasks,
            'cancelada' => $canceledTasks,
        ];

        // Tasa de cumplimiento
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        $onTimeRate = $completedTasks > 0 ? round(($onTimeTasks / $completedTasks) * 100, 1) : 0;
        $efficiencyRate = $completedTasks > 0 ? round(($efficientTasks / $completedTasks) * 100, 1) : 0;
        $delayedRate = $completedTasks > 0 ? round(($delayedTasks / $completedTasks) * 100, 1) : 0;

        $taskMetrics = [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'canceled_tasks' => $canceledTasks,
            'active_tasks' => $totalTasks - $completedTasks - $canceledTasks,
            'overdue_tasks' => $overdueTasks,

            // Métricas de eficiencia basadas en tiempo objetivo vs tiempo real
            'efficient_tasks' => $efficientTasks, // < 80% del tiempo objetivo
            'on_time_tasks' => $onTimeTasks, // <= 100% del tiempo objetivo
            'delayed_tasks' => $delayedTasks, // > 100% del tiempo objetivo
            'efficiency_rate' => $efficiencyRate, // % de tareas eficientes
            'on_time_rate' => $onTimeRate, // % de tareas a tiempo
            'delayed_rate' => $delayedRate, // % de tareas retrasadas
            'avg_efficiency' => $avgEfficiency, // Promedio de eficiencia (100% = perfecto)
            'avg_time_variance' => $avgTimeVariance, // Diferencia promedio en horas

            'completion_rate' => $completionRate,
            'avg_completion_time' => $avgCompletionTime,
            'by_status' => $tasksByStatus,
            'last_7_days' => $last7Days,
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'permissions' => $permissions,
            'taskMetrics' => $taskMetrics,
        ]);
    }
}
