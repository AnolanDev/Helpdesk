<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Board;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use App\Services\TaskTimeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    protected TaskTimeService $timeService;

    public function __construct(TaskTimeService $timeService)
    {
        $this->timeService = $timeService;
    }

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $user = auth()->user();
        $query = Task::with(['board.user', 'subTasks']);

        // Filtrar tareas según el tipo de usuario
        if ($user->isAdmin()) {
            // Administradores: ven tareas de sus tableros + tareas de tableros de usuarios tech
            $query->whereHas('board', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', function ($q) {
                      $q->where('tipo_usuario', 'tech');
                  });
            });
        } else {
            // Usuarios normales/tech: solo ven tareas de sus tableros
            $query->whereHas('board', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('board_id')) {
            $query->byBoard($request->board_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro de tareas vencidas
        if ($request->boolean('show_overdue')) {
            $query->overdue();
        }

        // Filtro de tareas activas
        if (!$request->filled('status')) {
            if (!$request->boolean('show_completed')) {
                $query->active();
            }
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowedSortColumns = ['title', 'status', 'priority', 'created_at', 'progress'];

        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $tasks = $query->paginate(15)->withQueryString();

        // Obtener estadísticas
        $statsQuery = Task::query();
        if ($user->isAdmin()) {
            // Administradores: estadísticas de sus tareas + tareas de usuarios tech
            $statsQuery->whereHas('board', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', function ($q) {
                      $q->where('tipo_usuario', 'tech');
                  });
            });
        } else {
            // Usuarios normales/tech: solo estadísticas de sus tareas
            $statsQuery->whereHas('board', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Obtener tableros para el filtro
        $boards = Board::query();
        if ($user->isAdmin()) {
            // Administradores: sus tableros + tableros de usuarios tech
            $boards->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', function ($q) {
                      $q->where('tipo_usuario', 'tech');
                  });
            });
        } else {
            // Usuarios normales/tech: solo sus tableros
            $boards->where('user_id', $user->id);
        }

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['status', 'priority', 'board_id', 'search', 'show_completed', 'show_overdue', 'sort_by', 'sort_dir']),
            'statuses' => Task::getStatuses(),
            'priorities' => Task::getPriorities(),
            'boards' => $boards->orderBy('name')->get(['id', 'name']),
            'stats' => [
                'creada' => (clone $statsQuery)->byStatus('creada')->count(),
                'analizada' => (clone $statsQuery)->byStatus('analizada')->count(),
                'programada' => (clone $statsQuery)->byStatus('programada')->count(),
                'en_progreso' => (clone $statsQuery)->byStatus('en_progreso')->count(),
                'finalizada' => (clone $statsQuery)->byStatus('finalizada')->count(),
                'cancelada' => (clone $statsQuery)->byStatus('cancelada')->count(),
                'overdue' => (clone $statsQuery)->overdue()->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Task::class);

        // Requiere board_id en la URL
        $request->validate([
            'board_id' => 'required|exists:boards,id',
        ]);

        $board = Board::with('user')->findOrFail($request->board_id);

        // Verificar que el usuario tiene acceso al tablero
        $this->authorize('view', $board);

        return Inertia::render('Tasks/Create', [
            'priorities' => Task::getPriorities(),
            'board' => $board,
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'board_id' => 'required|exists:boards,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:' . implode(',', array_keys(Task::getPriorities())),
        ]);

        // Verificar que el usuario tiene acceso al tablero
        $board = Board::findOrFail($validated['board_id']);
        $this->authorize('view', $board);

        $task = Task::create($validated);

        return redirect()->route('boards.show', $board->id)
            ->with('success', 'Tarea creada exitosamente.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['board.user', 'subTasks']);

        return Inertia::render('Tasks/Show', [
            'task' => $task,
            'statuses' => Task::getStatuses(),
            'priorities' => Task::getPriorities(),
        ]);
    }

    /**
     * Show the form for editing the task.
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $task->load('board.user');

        return Inertia::render('Tasks/Edit', [
            'task' => $task,
            'priorities' => Task::getPriorities(),
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
        ]);

        $task->update($validated);

        return redirect()->route('boards.show', $task->board_id)
            ->with('success', 'Tarea actualizada exitosamente.');
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $boardId = $task->board_id;
        $task->delete();

        return redirect()->route('boards.show', $boardId)
            ->with('success', 'Tarea eliminada exitosamente.');
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('updateStatus', $task);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Task::getStatuses())),
            'cancel_reason' => 'required_if:status,cancelada|nullable|string',
        ]);

        try {
            $task->changeStatus(
                TaskStatus::from($validated['status']),
                $validated['cancel_reason'] ?? null
            );

            return back()->with('success', 'Estado actualizado exitosamente.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get allowed transitions for a task.
     */
    public function getAllowedTransitions(Task $task)
    {
        $this->authorize('view', $task);

        $currentStatus = $task->status;
        $allowedTransitions = [];

        foreach (TaskStatus::cases() as $status) {
            if ($currentStatus->canTransitionTo($status)) {
                $allowedTransitions[] = [
                    'value' => $status->value,
                    'label' => $status->label(),
                    'color' => $status->color(),
                ];
            }
        }

        return response()->json([
            'current_status' => [
                'value' => $currentStatus->value,
                'label' => $currentStatus->label(),
            ],
            'allowed_transitions' => $allowedTransitions,
            'can_be_finalized' => $task->can_be_finalized,
        ]);
    }
}
