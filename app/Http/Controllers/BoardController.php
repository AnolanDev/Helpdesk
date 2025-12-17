<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BoardController extends Controller
{
    /**
     * Display a listing of boards.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Board::class);

        $user = auth()->user();
        $query = Board::with(['user'])->withCount(['tasks']);

        // Filtrar tableros según el tipo de usuario
        if ($user->isAdmin()) {
            // Administradores: ven sus propios tableros + tableros de usuarios tech + tableros compartidos con ellos
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', function ($q) {
                      $q->where('tipo_usuario', 'tech');
                  })
                  ->orWhereHas('sharedWith', function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
            });
        } else {
            // Usuarios normales/tech: ven sus propios tableros + tableros compartidos con ellos
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('sharedWith', function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
            });
        }

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowedSortColumns = ['name', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $boards = $query->paginate(15)->withQueryString();

        return Inertia::render('Boards/Index', [
            'boards' => $boards,
            'filters' => $request->only(['search', 'sort_by', 'sort_dir']),
        ]);
    }

    /**
     * Show the form for creating a new board.
     */
    public function create()
    {
        $this->authorize('create', Board::class);

        return Inertia::render('Boards/Create');
    }

    /**
     * Store a newly created board.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Board::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $board = Board::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('boards.show', $board)
            ->with('success', 'Tablero creado exitosamente.');
    }

    /**
     * Display the specified board.
     */
    public function show(Board $board)
    {
        $this->authorize('view', $board);

        $board->load(['user', 'tasks' => function ($query) {
            $query->with(['subTasks'])->orderBy('created_at', 'desc');
        }, 'sharedWith' => function ($query) {
            $query->select('users.id', 'users.name', 'users.email', 'users.tipo_usuario');
        }]);

        // Determinar el permiso del usuario actual
        $currentUserPermission = $board->getPermissionFor(auth()->user());

        return Inertia::render('Boards/Show', [
            'board' => $board,
            'currentUserPermission' => $currentUserPermission,
            'canShare' => auth()->user()->can('share', $board),
        ]);
    }

    /**
     * Show the form for editing the board.
     */
    public function edit(Board $board)
    {
        $this->authorize('update', $board);

        $board->load('user');

        return Inertia::render('Boards/Edit', [
            'board' => $board,
        ]);
    }

    /**
     * Update the specified board.
     */
    public function update(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $board->update($validated);

        return redirect()->route('boards.show', $board)
            ->with('success', 'Tablero actualizado exitosamente.');
    }

    /**
     * Remove the specified board.
     */
    public function destroy(Board $board)
    {
        $this->authorize('delete', $board);

        $board->delete();

        return redirect()->route('boards.index')
            ->with('success', 'Tablero eliminado exitosamente.');
    }

    /**
     * Get users available to share with.
     */
    public function getAvailableUsers(Board $board)
    {
        $this->authorize('share', $board);

        $user = auth()->user();

        // Obtener usuarios disponibles (excluyendo el propietario y ya compartidos)
        $sharedUserIds = $board->sharedWith()->pluck('user_id')->toArray();
        $excludedIds = array_merge([$board->user_id], $sharedUserIds);

        $availableUsers = User::whereNotIn('id', $excludedIds)
            ->where('is_active', true)
            ->select('id', 'name', 'email', 'tipo_usuario')
            ->orderBy('name')
            ->get();

        return response()->json([
            'users' => $availableUsers,
        ]);
    }

    /**
     * Share board with a user.
     */
    public function share(Request $request, Board $board)
    {
        $this->authorize('share', $board);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|in:read,write',
        ]);

        // No permitir compartir con el mismo propietario
        if ($validated['user_id'] == $board->user_id) {
            return back()->with('error', 'No puedes compartir el tablero contigo mismo.');
        }

        $board->shareWith(
            User::find($validated['user_id']),
            $validated['permission']
        );

        return back()->with('success', 'Tablero compartido exitosamente.');
    }

    /**
     * Update share permission.
     */
    public function updateShare(Request $request, Board $board, User $sharedUser)
    {
        $this->authorize('share', $board);

        $validated = $request->validate([
            'permission' => 'required|in:read,write',
        ]);

        $board->updatePermissionFor($sharedUser, $validated['permission']);

        return back()->with('success', 'Permisos actualizados exitosamente.');
    }

    /**
     * Remove share access.
     */
    public function unshare(Board $board, User $sharedUser)
    {
        $this->authorize('share', $board);

        $board->unshareWith($sharedUser);

        return back()->with('success', 'Acceso removido exitosamente.');
    }
}
