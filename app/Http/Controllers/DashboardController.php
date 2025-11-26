<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'permissions' => $permissions,
        ]);
    }
}
