<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Solo administradores pueden ver el listado de usuarios
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Administradores pueden ver cualquier usuario
        // Los usuarios pueden ver su propio perfil
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo administradores pueden crear usuarios
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Administradores pueden editar cualquier usuario
        // Los usuarios pueden editar su propio perfil
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Solo administradores pueden eliminar usuarios
        // No se puede eliminar a sí mismo
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        // Solo administradores pueden restaurar usuarios
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Solo administradores pueden eliminar permanentemente
        // No se puede eliminar a sí mismo
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can change user type.
     */
    public function changeTipoUsuario(User $user, User $model): bool
    {
        // Solo administradores pueden cambiar el tipo de usuario
        // No se puede cambiar su propio tipo
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
