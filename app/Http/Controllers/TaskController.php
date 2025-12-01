<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Task::with(['creator', 'assignedUser']);

        // Filtrar tareas según el rol del usuario
        if (!$user->isAdmin()) {
            // Usuarios normales: ven sus tareas creadas + tareas asignadas a ellos
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }
        // Admin: ve todas las tareas (no aplica filtro)

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('assigned_to')) {
            $assignedTo = $request->assigned_to === 'me' ? auth()->id() : $request->assigned_to;
            $query->assignedTo($assignedTo);
        }

        if ($request->filled('created_by')) {
            $createdBy = $request->created_by === 'me' ? auth()->id() : $request->created_by;
            $query->createdBy($createdBy);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('task_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro de tareas vencidas
        $showOverdue = $request->boolean('show_overdue');
        if ($showOverdue) {
            $query->overdue();
        }

        // Filtro de tareas activas/completadas
        $showCompleted = $request->boolean('show_completed');
        if (!$request->filled('status')) {
            if ($request->filled('show_completed')) {
                if (!$showCompleted) {
                    $query->active();
                }
            } else {
                // Por defecto, mostrar solo tareas activas
                if (!$showOverdue) {
                    $query->active();
                }
            }
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowedSortColumns = [
            'task_number',
            'title',
            'status',
            'priority',
            'due_date',
            'created_at',
            'updated_at',
        ];

        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $tasks = $query->paginate(15)->withQueryString();

        // Calcular estadísticas según los permisos del usuario
        $statsQuery = Task::query();
        if (!$user->isAdmin()) {
            $statsQuery->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['status', 'priority', 'assigned_to', 'created_by', 'search', 'show_completed', 'show_overdue', 'sort_by', 'sort_dir']),
            'statuses' => Task::getStatuses(),
            'priorities' => Task::getPriorities(),
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
            'stats' => [
                'todo' => (clone $statsQuery)->todo()->count(),
                'in_progress' => (clone $statsQuery)->inProgress()->count(),
                'in_review' => (clone $statsQuery)->inReview()->count(),
                'overdue' => (clone $statsQuery)->overdue()->count(),
            ],
        ]);
    }

    /**
     * Display tasks in a board view (Kanban style).
     */
    public function board(Request $request)
    {
        $user = auth()->user();
        $query = Task::with(['creator', 'assignedUser']);

        // Filtrar tareas según el rol del usuario
        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        // Filtros aplicables
        if ($request->filled('assigned_to')) {
            $assignedTo = $request->assigned_to === 'me' ? auth()->id() : $request->assigned_to;
            $query->assignedTo($assignedTo);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        // Obtener tareas activas agrupadas por estado
        $tasks = $query->active()->orderBy('position', 'asc')->get()->groupBy('status');

        return Inertia::render('Tasks/Board', [
            'tasks' => [
                'todo' => $tasks->get(Task::STATUS_TODO, collect())->values(),
                'in_progress' => $tasks->get(Task::STATUS_IN_PROGRESS, collect())->values(),
                'review' => $tasks->get(Task::STATUS_REVIEW, collect())->values(),
            ],
            'filters' => $request->only(['assigned_to', 'priority']),
            'priorities' => Task::getPriorities(),
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        return Inertia::render('Tasks/Create', [
            'priorities' => Task::getPriorities(),
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
            'empresas' => Task::getEmpresas(),
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:' . implode(',', array_keys(Task::getPriorities())),
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'empresa' => 'nullable|string|in:' . implode(',', Task::getEmpresas()),
            'sucursal' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'labels' => 'nullable|array',
        ]);

        $task = Task::create([
            ...$validated,
            'created_by' => auth()->id(),
            'status' => Task::STATUS_TODO,
        ]);

        // Si se asigna directamente
        if (!empty($validated['assigned_to'])) {
            $assignedUser = User::find($validated['assigned_to']);
            $task->assignTo($assignedUser);

            // Notificar asignación
            \App\Models\Notification::create([
                'user_id' => $assignedUser->id,
                'type' => 'task_assigned',
                'title' => 'Tarea asignada',
                'message' => "Se te ha asignado la tarea: {$task->title}",
                'data' => [
                    'task_id' => $task->id,
                    'task_number' => $task->task_number,
                    'assigned_by' => auth()->user()->name,
                ],
            ]);
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Tarea creada exitosamente.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load([
            'creator',
            'assignedUser',
            'comments' => function ($query) {
                $query->with('user')->orderBy('created_at', 'asc');
            },
        ]);

        $user = auth()->user();

        return Inertia::render('Tasks/Show', [
            'task' => $task,
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
            'statuses' => Task::getStatuses(),
            'priorities' => Task::getPriorities(),
            'canEdit' => $user->can('update', $task),
        ]);
    }

    /**
     * Show the form for editing the task.
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        return Inertia::render('Tasks/Edit', [
            'task' => $task,
            'priorities' => Task::getPriorities(),
            'users' => User::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:' . implode(',', array_keys(Task::getPriorities())),
            'due_date' => 'nullable|date',
            'labels' => 'nullable|array',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Tarea actualizada exitosamente.');
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('updateStatus', $task);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Task::getStatuses())),
        ]);

        $oldStatus = $task->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->with('info', 'La tarea ya tiene ese estado.');
        }

        $oldStatusLabel = $task->status_label;
        $task->update(['status' => $newStatus]);

        // Agregar comentario automático del cambio de estado
        $task->addComment(
            "Estado cambiado de '{$oldStatusLabel}' a '{$task->status_label}'",
            'status_change'
        );

        // Notificar cambio de estado
        $notifyUserId = $task->assigned_to ?? $task->created_by;
        if ($notifyUserId && $notifyUserId !== auth()->id()) {
            \App\Models\Notification::create([
                'user_id' => $notifyUserId,
                'type' => 'task_status_changed',
                'title' => 'Estado de tarea actualizado',
                'message' => "La tarea '{$task->title}' cambió a: {$task->status_label}",
                'data' => [
                    'task_id' => $task->id,
                    'task_number' => $task->task_number,
                    'old_status' => $oldStatusLabel,
                    'new_status' => $task->status_label,
                ],
            ]);
        }

        return back()->with('success', 'Estado actualizado exitosamente.');
    }

    /**
     * Assign task to a user.
     */
    public function assign(Request $request, Task $task)
    {
        $this->authorize('assign', $task);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $oldAssignee = $task->assigned_to ? User::find($task->assigned_to) : null;
        $newAssignee = User::find($validated['assigned_to']);

        if ($oldAssignee && $oldAssignee->id === $newAssignee->id) {
            return back()->with('info', "La tarea ya está asignada a {$newAssignee->name}.");
        }

        $task->assignTo($newAssignee);

        // Notificar al nuevo asignado
        \App\Models\Notification::create([
            'user_id' => $newAssignee->id,
            'type' => 'task_assigned',
            'title' => 'Tarea asignada',
            'message' => "Se te ha asignado la tarea: {$task->title}",
            'data' => [
                'task_id' => $task->id,
                'task_number' => $task->task_number,
                'assigned_by' => auth()->user()->name,
            ],
        ]);

        return back()->with('success', "Tarea asignada a {$newAssignee->name}.");
    }

    /**
     * Add comment to task.
     */
    public function addComment(Request $request, Task $task)
    {
        $this->authorize('addComment', $task);

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $task->addComment($validated['comment']);

        // Notificar nuevo comentario
        $notifyUserId = ($task->assigned_to !== auth()->id()) ? $task->assigned_to : $task->created_by;
        if ($notifyUserId && $notifyUserId !== auth()->id()) {
            \App\Models\Notification::create([
                'user_id' => $notifyUserId,
                'type' => 'task_commented',
                'title' => 'Nuevo comentario en tarea',
                'message' => auth()->user()->name . " comentó en: {$task->title}",
                'data' => [
                    'task_id' => $task->id,
                    'task_number' => $task->task_number,
                    'commented_by' => auth()->user()->name,
                ],
            ]);
        }

        return back()->with('success', 'Comentario agregado exitosamente.');
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Tarea eliminada exitosamente.');
    }

    /**
     * Update task position (for Kanban board drag & drop).
     */
    public function updatePosition(Request $request, Task $task)
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:0',
            'status' => 'required|in:' . implode(',', array_keys(Task::getStatuses())),
        ]);

        $oldStatus = $task->status;
        $newStatus = $validated['status'];

        $task->update([
            'position' => $validated['position'],
            'status' => $validated['status'],
        ]);

        // Si el estado cambió, agregar comentario automático
        if ($oldStatus !== $newStatus) {
            $oldStatusLabel = Task::getStatuses()[$oldStatus];
            $newStatusLabel = Task::getStatuses()[$newStatus];

            $task->addComment(
                "Estado cambiado de '{$oldStatusLabel}' a '{$newStatusLabel}' (tablero Kanban)",
                'status_change'
            );

            // Notificar cambio de estado
            $notifyUserId = $task->assigned_to ?? $task->created_by;
            if ($notifyUserId && $notifyUserId !== auth()->id()) {
                \App\Models\Notification::create([
                    'user_id' => $notifyUserId,
                    'type' => 'task_status_changed',
                    'title' => 'Estado de tarea actualizado',
                    'message' => "La tarea '{$task->title}' cambió a: {$newStatusLabel}",
                    'data' => [
                        'task_id' => $task->id,
                        'task_number' => $task->task_number,
                        'old_status' => $oldStatusLabel,
                        'new_status' => $newStatusLabel,
                    ],
                ]);
            }
        }

        return back();
    }
}
