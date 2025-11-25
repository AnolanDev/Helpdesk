<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->orderBy('name', 'asc');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('sucursal', 'like', "%{$search}%")
                    ->orWhere('empresa', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo_usuario')) {
            $query->where('tipo_usuario', $request->tipo_usuario);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        $users = $query->paginate(15)->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'tipo_usuario', 'is_active']),
            'tiposUsuario' => User::getTiposUsuario(),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create', [
            'tiposUsuario' => User::getTiposUsuario(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'mobile' => 'nullable|string|max:255',
            'sucursal' => 'nullable|string|max:255',
            'empresa' => 'nullable|string|max:255',
            'tipo_usuario' => 'required|in:' . implode(',', array_keys(User::getTiposUsuario())),
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return Inertia::render('Users/Show', [
            'user' => $user,
            'tiposUsuario' => User::getTiposUsuario(),
        ]);
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'tiposUsuario' => User::getTiposUsuario(),
            'canChangeTipoUsuario' => auth()->user()->can('changeTipoUsuario', $user),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'mobile' => 'nullable|string|max:255',
            'sucursal' => 'nullable|string|max:255',
            'empresa' => 'nullable|string|max:255',
            'tipo_usuario' => 'required|in:' . implode(',', array_keys(User::getTiposUsuario())),
            'is_active' => 'boolean',
        ]);

        // Verificar si puede cambiar el tipo de usuario
        if (isset($validated['tipo_usuario']) && $validated['tipo_usuario'] !== $user->tipo_usuario) {
            $this->authorize('changeTipoUsuario', $user);
        }

        // Solo actualizar password si se proporcionó
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Soft delete
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
