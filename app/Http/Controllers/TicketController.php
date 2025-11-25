<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $user = auth()->user();
        $query = Ticket::with(['user', 'assignedUser'])
            ->orderBy('created_at', 'desc');

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

        // Filtro de tickets abiertos/cerrados
        if ($request->filled('show_closed')) {
            if (!$request->show_closed) {
                $query->open();
            }
        } else {
            // Por defecto, mostrar solo tickets abiertos
            $query->open();
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
            'filters' => $request->only(['status', 'priority', 'category', 'assigned_to', 'search', 'show_closed']),
            'statuses' => Ticket::getStatuses(),
            'priorities' => Ticket::getPriorities(),
            'categories' => Ticket::getCategories(),
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
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
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
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

        $ticket = Ticket::create([
            ...$validated,
            'user_id' => auth()->id(),
            'status' => $validated['assigned_to'] ?? null
                ? Ticket::STATUS_OPEN
                : Ticket::STATUS_NEW,
        ]);

        // Si se asigna directamente
        if (!empty($validated['assigned_to'])) {
            $assignedUser = User::find($validated['assigned_to']);
            $ticket->assignTo($assignedUser);

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
        ]);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
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
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
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

        $ticket->update($validated);

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
        $ticket->update(['status' => $validated['status']]);

        // Agregar comentario automático del cambio de estado
        $ticket->addComment(
            "Estado cambiado de '{$ticket->getStatusLabelAttribute()}' a '{$ticket->status_label}'",
            'status_change',
            true
        );

        // Notificar cambio de estado
        \App\Models\Notification::notifyTicketStatusChanged($ticket, $oldStatus, $validated['status'], auth()->user());

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

        // Si ya estaba asignado, es una reasignación
        if ($oldAssignee && $oldAssignee->id !== $newAssignee->id) {
            \App\Models\Notification::notifyTicketReassigned($ticket, $oldAssignee, $newAssignee, auth()->user());
        } elseif (!$oldAssignee) {
            // Primera asignación
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
}
