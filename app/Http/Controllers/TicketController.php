<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Exports\TicketActivitiesExport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $user = auth()->user();
        $query = Ticket::with(['user', 'assignedUser']);

        // Filtrar tickets según el tipo de usuario
        if ($user->isUsuarioFinal()) {
            // Usuario final: solo ve sus propios tickets
            $query->where('user_id', $user->id);
        } elseif ($user->isTech()) {
            // Técnico: ve sus tickets creados + tickets asignados a él
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }
        // Admin: ve todos los tickets (no aplica filtro)

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('assigned_to')) {
            $assignedTo = $request->assigned_to === 'me' ? auth()->id() : $request->assigned_to;
            $query->assignedTo($assignedTo);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        // Filtro de tickets vencidos
        // IMPORTANTE: Convertir string "false"/"true" a boolean real
        $showOverdue = $request->boolean('show_overdue');
        if ($showOverdue) {
            $query->overdue();
        }

        // Filtro de tickets abiertos/cerrados
        // Solo aplicar el filtro open() si NO se está filtrando por un estado específico
        // IMPORTANTE: Convertir string "false"/"true" a boolean real
        $showClosed = $request->boolean('show_closed');
        if (!$request->filled('status')) {
            if ($request->filled('show_closed')) {
                if (!$showClosed) {
                    $query->open();
                }
            } else {
                // Por defecto, mostrar solo tickets abiertos (a menos que se esté filtrando por vencidos)
                if (!$showOverdue) {
                    $query->open();
                }
            }
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        // Validar columnas permitidas para ordenamiento
        $allowedSortColumns = [
            'user_name',
            'ticket_number',
            'title',
            'status',
            'priority',
            'empresa',
            'sucursal',
            'created_at',
            'updated_at',
        ];

        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $tickets = $query->paginate(15)->withQueryString();

        // Calcular estadísticas según los permisos del usuario
        $statsQuery = Ticket::query();
        if ($user->isUsuarioFinal()) {
            $statsQuery->where('user_id', $user->id);
        } elseif ($user->isTech()) {
            $statsQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'category', 'assigned_to', 'search', 'show_closed', 'show_overdue', 'sort_by', 'sort_dir']),
            'statuses' => Ticket::getStatuses(),
            'priorities' => Ticket::getPriorities(),
            'categories' => Ticket::getCategories(),
            'users' => User::active()->techs()->orderBy('name')->get(['id', 'name']),
            'slaWarningHours' => \App\Models\Setting::get('sla_warning_hours', 24),
            'stats' => [
                'open' => (clone $statsQuery)->open()->count(),
                'in_progress' => (clone $statsQuery)->byStatus(Ticket::STATUS_IN_PROGRESS)->count(),
                'pending' => (clone $statsQuery)->byStatus(Ticket::STATUS_PENDING)->count(),
                'overdue' => (clone $statsQuery)->overdue()->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        $this->authorize('create', Ticket::class);

        return Inertia::render('Tickets/Create', [
            'priorities' => Ticket::getPriorities(),
            'categories' => Ticket::getCategories(),
            'users' => User::active()->techs()->orderBy('name')->get(['id', 'name']),
            'empresas' => Ticket::getEmpresas(),
            'sucursalesByEmpresa' => Ticket::getSucursalesByEmpresa(),
        ]);
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Ticket::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:' . implode(',', array_keys(Ticket::getPriorities())),
            'category' => 'required|in:' . implode(',', array_keys(Ticket::getCategories())),
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'empresa' => 'required|string|in:' . implode(',', Ticket::getEmpresas()),
            'sucursal' => 'required|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Intentar crear el ticket con reintentos en caso de duplicado
        $maxRetries = 3;
        $ticket = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $ticket = Ticket::create([
                    ...$validated,
                    'user_id' => auth()->id(),
                    'status' => $validated['assigned_to'] ?? null
                        ? Ticket::STATUS_OPEN
                        : Ticket::STATUS_NEW,
                ]);

                break; // Si se crea exitosamente, salir del loop
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Si es el último intento, lanzar el error
                if ($attempt === $maxRetries) {
                    throw $e;
                }
                // Si no, esperar un poco y reintentar (para evitar race conditions)
                usleep(100000); // 100ms
            }
        }

        // Log ticket creation
        TicketActivity::logCreated($ticket, auth()->user());

        // Si se asigna directamente
        if (!empty($validated['assigned_to'])) {
            $assignedUser = User::find($validated['assigned_to']);
            $ticket->assignTo($assignedUser);

            // Log assignment
            TicketActivity::logAssigned($ticket, $assignedUser, auth()->user());

            // Notificar asignación
            \App\Models\Notification::notifyTicketAssigned($ticket, $assignedUser, auth()->user());
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket creado exitosamente.');
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'user',
            'assignedUser',
            'comments' => function ($query) {
                $query->with('user')->orderBy('created_at', 'asc');
            },
            'activities' => function ($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
        ]);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'users' => User::active()->techs()->orderBy('name')->get(['id', 'name']),
            'statuses' => Ticket::getStatuses(),
            'priorities' => Ticket::getPriorities(),
            'canEdit' => auth()->user()->can('update', $ticket),
        ]);
    }

    /**
     * Show the form for editing the ticket.
     */
    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket,
            'priorities' => Ticket::getPriorities(),
            'categories' => Ticket::getCategories(),
            'users' => User::active()->techs()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified ticket.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:' . implode(',', array_keys(Ticket::getPriorities())),
            'category' => 'required|in:' . implode(',', array_keys(Ticket::getCategories())),
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        // Track changes for activity log
        $oldPriority = $ticket->priority;
        $oldCategory = $ticket->category;

        // Recalcular due_date si cambió la prioridad y no se especificó un due_date manual
        if ($oldPriority !== $validated['priority'] && !$request->filled('due_date')) {
            $validated['due_date'] = Ticket::calculateDueDate($validated['priority']);
        }

        $ticket->update($validated);

        // Log specific changes
        if ($oldPriority !== $validated['priority']) {
            TicketActivity::logPriorityChanged($ticket, $oldPriority, $validated['priority'], auth()->user());
        }

        if ($oldCategory !== $validated['category']) {
            TicketActivity::logCategoryChanged($ticket, $oldCategory, $validated['category'], auth()->user());
        }

        // Log general update if other fields changed
        $changes = [];
        foreach (['title', 'description', 'location', 'department', 'due_date'] as $field) {
            if ($ticket->getOriginal($field) !== $validated[$field] ?? null) {
                $changes[$field] = $validated[$field] ?? null;
            }
        }

        if (!empty($changes)) {
            TicketActivity::logUpdated($ticket, auth()->user(), $changes);
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket actualizado exitosamente.');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorize('updateStatus', $ticket);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Ticket::getStatuses())),
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];

        // Verificar si el estado realmente cambió
        if ($oldStatus === $newStatus) {
            return back()->with('info', 'El ticket ya tiene ese estado.');
        }

        // Obtener labels antes de actualizar
        $oldStatusLabel = $ticket->status_label;

        $ticket->update(['status' => $newStatus]);

        // Log status change
        TicketActivity::logStatusChanged($ticket, $oldStatus, $newStatus, auth()->user());

        // Agregar comentario automático del cambio de estado
        $ticket->addComment(
            "Estado cambiado de '{$oldStatusLabel}' a '{$ticket->status_label}'",
            'status_change',
            true
        );

        // Notificar cambio de estado
        \App\Models\Notification::notifyTicketStatusChanged($ticket, $oldStatus, $newStatus, auth()->user());

        return back()->with('success', 'Estado actualizado exitosamente.');
    }

    /**
     * Assign ticket to a user.
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $oldAssignee = $ticket->assigned_to ? User::find($ticket->assigned_to) : null;
        $newAssignee = User::find($validated['assigned_to']);

        // Verificar si está asignando al mismo usuario
        if ($oldAssignee && $oldAssignee->id === $newAssignee->id) {
            return back()->with('info', "El ticket ya está asignado a {$newAssignee->name}.");
        }

        // Si ya estaba asignado, es una reasignación
        if ($oldAssignee && $oldAssignee->id !== $newAssignee->id) {
            // Log reassignment
            TicketActivity::logReassigned($ticket, $oldAssignee, $newAssignee, auth()->user());
            \App\Models\Notification::notifyTicketReassigned($ticket, $oldAssignee, $newAssignee, auth()->user());
        } elseif (!$oldAssignee) {
            // Primera asignación
            TicketActivity::logAssigned($ticket, $newAssignee, auth()->user());
            \App\Models\Notification::notifyTicketAssigned($ticket, $newAssignee, auth()->user());
        }

        $ticket->assignTo($newAssignee);

        // Si el usuario actual pierde acceso al ticket después de reasignarlo, redirigir al índice
        $user = auth()->user();
        $canStillView = $user->id === $ticket->user_id
            || $user->id === $ticket->assigned_to
            || $user->isAdmin();

        if ($canStillView) {
            return back()->with('success', "Ticket asignado a {$newAssignee->name}.");
        } else {
            return redirect()->route('tickets.index')
                ->with('success', "Ticket asignado a {$newAssignee->name}. Has sido redirigido a la lista de tickets.");
        }
    }

    /**
     * Add comment to ticket.
     */
    public function addComment(Request $request, Ticket $ticket)
    {
        $this->authorize('addComment', $ticket);

        $validated = $request->validate([
            'comment' => 'required|string',
            'type' => 'required|in:public,internal,solution',
            'is_private' => 'boolean',
        ]);

        // Verificar si puede agregar comentarios privados
        if (($validated['is_private'] ?? false) && !auth()->user()->can('addPrivateComment', $ticket)) {
            abort(403, 'No tienes permiso para agregar comentarios privados.');
        }

        $ticket->addComment(
            $validated['comment'],
            $validated['type'],
            $validated['is_private'] ?? false
        );

        // Log comment
        TicketActivity::logCommented($ticket, auth()->user(), $validated['type']);

        // Notificar nuevo comentario (solo si no es privado)
        if (!($validated['is_private'] ?? false)) {
            \App\Models\Notification::notifyTicketCommented($ticket, auth()->user());
        }

        return back()->with('success', 'Comentario agregado exitosamente.');
    }

    /**
     * Mark ticket as resolved.
     */
    public function resolve(Request $request, Ticket $ticket)
    {
        $this->authorize('resolve', $ticket);

        $validated = $request->validate([
            'solution' => 'nullable|string',
        ]);

        $ticket->markAsResolved($validated['solution'] ?? null);

        // Log resolution
        TicketActivity::logResolved($ticket, auth()->user());

        // Notificar resolución
        \App\Models\Notification::notifyTicketResolved($ticket, auth()->user());

        return back()->with('success', 'Ticket marcado como resuelto.');
    }

    /**
     * Mark ticket as closed.
     */
    public function close(Ticket $ticket)
    {
        $this->authorize('close', $ticket);

        $ticket->markAsClosed();

        // Log closure
        TicketActivity::logClosed($ticket, auth()->user());

        return back()->with('success', 'Ticket cerrado exitosamente.');
    }

    /**
     * Reopen a ticket.
     */
    public function reopen(Ticket $ticket)
    {
        $this->authorize('reopen', $ticket);

        if (!$ticket->isClosed() && !$ticket->isResolved()) {
            return back()->with('error', 'Solo se pueden reabrir tickets cerrados o resueltos.');
        }

        $ticket->reopen();

        // Log reopening
        TicketActivity::logReopened($ticket, auth()->user());

        return back()->with('success', 'Ticket reabierto exitosamente.');
    }

    /**
     * Remove the specified ticket.
     */
    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket eliminado exitosamente.');
    }

    /**
     * Export ticket activities
     */
    public function exportActivities(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $format = $request->query('format', 'excel');

        if ($format === 'excel') {
            return Excel::download(
                new TicketActivitiesExport($ticket->id),
                "ticket-{$ticket->ticket_number}-actividades.xlsx"
            );
        } elseif ($format === 'pdf') {
            $activities = TicketActivity::where('ticket_id', $ticket->id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            $pdf = Pdf::loadView('exports.ticket-activities', [
                'ticket' => $ticket,
                'activities' => $activities,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("ticket-{$ticket->ticket_number}-actividades.pdf");
        }

        abort(400, 'Invalid export format');
    }
}
