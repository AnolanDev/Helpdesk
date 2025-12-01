<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de tareas
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Admin puede ver cualquier tarea
        if ($user->isAdmin()) {
            return true;
        }

        // El usuario puede ver si es el creador o el asignado
        return $task->created_by === $user->id || $task->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear tareas
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Admin puede actualizar cualquier tarea
        if ($user->isAdmin()) {
            return true;
        }

        // Solo el creador puede actualizar la tarea
        return $task->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Admin puede eliminar cualquier tarea
        if ($user->isAdmin()) {
            return true;
        }

        // Solo el creador puede eliminar la tarea
        return $task->created_by === $user->id;
    }

    /**
     * Determine whether the user can update the status.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        // Admin puede cambiar el estado de cualquier tarea
        if ($user->isAdmin()) {
            return true;
        }

        // El creador y el asignado pueden cambiar el estado
        return $task->created_by === $user->id || $task->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can assign the task.
     */
    public function assign(User $user, Task $task): bool
    {
        // Admin puede asignar cualquier tarea
        if ($user->isAdmin()) {
            return true;
        }

        // Solo el creador puede asignar la tarea
        return $task->created_by === $user->id;
    }

    /**
     * Determine whether the user can add comments.
     */
    public function addComment(User $user, Task $task): bool
    {
        // Admin puede comentar en cualquier tarea
        if ($user->isAdmin()) {
            return true;
        }

        // El creador y el asignado pueden comentar
        return $task->created_by === $user->id || $task->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        // Solo admin puede restaurar tareas eliminadas
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        // Solo admin puede eliminar permanentemente
        return $user->isAdmin();
    }
}
