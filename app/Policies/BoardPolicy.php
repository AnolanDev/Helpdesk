<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BoardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any boards.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de tableros
        return true;
    }

    /**
     * Determine if the user can view the board.
     */
    public function view(User $user, Board $board): bool
    {
        // El usuario puede ver el tablero si:
        // 1. Es el dueño del tablero
        // 2. El tablero está compartido con él (con cualquier permiso)
        // 3. Es administrador y el tablero pertenece a un usuario tech
        if ($user->id === $board->user_id) {
            return true;
        }

        // Verificar si el tablero está compartido con el usuario
        if ($board->isSharedWith($user)) {
            return true;
        }

        if ($user->isAdmin()) {
            // Los admins pueden ver tableros de usuarios tech
            return $board->user->isTech();
        }

        return false;
    }

    /**
     * Determine if the user can create boards.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios activos pueden crear tableros
        return $user->is_active ?? true;
    }

    /**
     * Determine if the user can update the board.
     */
    public function update(User $user, Board $board): bool
    {
        // El usuario puede editar el tablero si:
        // 1. Es el dueño del tablero
        // 2. El tablero está compartido con él con permisos de escritura
        // Los administradores NO pueden editar tableros de otros
        if ($user->id === $board->user_id) {
            return true;
        }

        return $board->hasWritePermission($user);
    }

    /**
     * Determine if the user can delete the board.
     */
    public function delete(User $user, Board $board): bool
    {
        // Solo el dueño del tablero puede eliminar
        // Los administradores NO pueden eliminar tableros de otros
        return $user->id === $board->user_id;
    }

    /**
     * Determine if the user can restore the board.
     */
    public function restore(User $user, Board $board): bool
    {
        // Solo el dueño del tablero puede restaurar
        return $user->id === $board->user_id;
    }

    /**
     * Determine if the user can permanently delete the board.
     */
    public function forceDelete(User $user, Board $board): bool
    {
        // Solo el dueño puede eliminar permanentemente
        return $user->id === $board->user_id;
    }

    /**
     * Determine if the user can share the board.
     */
    public function share(User $user, Board $board): bool
    {
        // Solo el dueño del tablero puede compartirlo
        return $user->id === $board->user_id;
    }
}
