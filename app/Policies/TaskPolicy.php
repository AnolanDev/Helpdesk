<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de tareas
        return true;
    }

    /**
     * Determine if the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        // El usuario puede ver la tarea si:
        // 1. Es el dueño del tablero al que pertenece
        // 2. El tablero está compartido con él (con cualquier permiso)
        // 3. Es administrador y el tablero pertenece a un usuario tech
        if ($user->id === $task->board->user_id) {
            return true;
        }

        // Verificar si el tablero está compartido con el usuario
        if ($task->board->isSharedWith($user)) {
            return true;
        }

        if ($user->isAdmin()) {
            // Los admins pueden ver tareas de tableros de usuarios tech
            return $task->board->user->isTech();
        }

        return false;
    }

    /**
     * Determine if the user can create tasks.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios activos pueden crear tareas
        return $user->is_active ?? true;
    }

    /**
     * Determine if the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        // El usuario puede editar la tarea si:
        // 1. Es el dueño del tablero
        // 2. El tablero está compartido con él con permisos de escritura
        // Los administradores NO pueden editar tareas de otros
        if ($user->id === $task->board->user_id) {
            return true;
        }

        return $task->board->hasWritePermission($user);
    }

    /**
     * Determine if the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        // El usuario puede eliminar la tarea si:
        // 1. Es el dueño del tablero
        // 2. El tablero está compartido con él con permisos de escritura
        // Los administradores NO pueden eliminar tareas de otros
        if ($user->id === $task->board->user_id) {
            return true;
        }

        return $task->board->hasWritePermission($user);
    }

    /**
     * Determine if the user can restore the task.
     */
    public function restore(User $user, Task $task): bool
    {
        // Solo el dueño del tablero puede restaurar
        return $user->id === $task->board->user_id;
    }

    /**
     * Determine if the user can permanently delete the task.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        // Solo el dueño puede eliminar permanentemente
        return $user->id === $task->board->user_id;
    }

    /**
     * Determine if the user can update the task status.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        // El usuario puede cambiar el estado si:
        // 1. Es el dueño del tablero
        // 2. El tablero está compartido con él con permisos de escritura
        // Los administradores NO pueden cambiar estados de tareas de otros
        if ($user->id === $task->board->user_id) {
            return true;
        }

        return $task->board->hasWritePermission($user);
    }

    /**
     * Determine if the user can manage sub-tasks.
     */
    public function manageSubTasks(User $user, Task $task): bool
    {
        // El usuario puede gestionar sub-tareas si:
        // 1. Es el dueño del tablero
        // 2. El tablero está compartido con él con permisos de escritura
        // Los administradores NO pueden gestionar sub-tareas de otros
        if ($user->id === $task->board->user_id) {
            return true;
        }

        return $task->board->hasWritePermission($user);
    }
}
