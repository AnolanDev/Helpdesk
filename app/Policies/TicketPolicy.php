<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any tickets.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de tickets
        // El controlador filtrará según permisos
        return true;
    }

    /**
     * Determine if the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // El usuario puede ver el ticket si:
        // 1. Es el creador
        // 2. Está asignado al ticket
        // 3. Es administrador
        return $user->id === $ticket->user_id
            || $user->id === $ticket->assigned_to
            || $user->isAdmin();
    }

    /**
     * Determine if the user can create tickets.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios activos pueden crear tickets
        return $user->is_active ?? true;
    }

    /**
     * Determine if the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // El usuario puede editar el ticket si:
        // 1. Es el creador
        // 2. Está asignado al ticket
        // 3. Es administrador
        return $user->id === $ticket->user_id
            || $user->id === $ticket->assigned_to
            || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the ticket.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Solo el creador o un administrador pueden eliminar
        return $user->id === $ticket->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can restore the ticket.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        // Solo administradores pueden restaurar tickets eliminados
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the ticket.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        // Solo administradores pueden eliminar permanentemente
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update the ticket status.
     */
    public function updateStatus(User $user, Ticket $ticket): bool
    {
        // El usuario puede cambiar el estado si:
        // 1. Está asignado al ticket (técnico)
        // 2. Es administrador
        // El creador NO puede cambiar el estado (solo el técnico asignado)
        return $user->id === $ticket->assigned_to || $user->isAdmin();
    }

    /**
     * Determine if the user can assign the ticket.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        // Solo administradores y técnicos pueden asignar tickets
        return $user->isAdmin() || $user->isTech();
    }

    /**
     * Determine if the user can resolve the ticket.
     */
    public function resolve(User $user, Ticket $ticket): bool
    {
        // El usuario puede resolver el ticket si:
        // 1. Está asignado al ticket
        // 2. Es administrador
        return $user->id === $ticket->assigned_to || $user->isAdmin();
    }

    /**
     * Determine if the user can close the ticket.
     */
    public function close(User $user, Ticket $ticket): bool
    {
        // Cualquier persona relacionada con el ticket puede cerrarlo:
        // 1. El creador
        // 2. El asignado
        // 3. Un administrador
        return $user->id === $ticket->user_id
            || $user->id === $ticket->assigned_to
            || $user->isAdmin();
    }

    /**
     * Determine if the user can reopen the ticket.
     */
    public function reopen(User $user, Ticket $ticket): bool
    {
        // El creador o un administrador pueden reabrir un ticket
        return $user->id === $ticket->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can add comments to the ticket.
     */
    public function addComment(User $user, Ticket $ticket): bool
    {
        // Puede comentar si puede ver el ticket
        return $this->view($user, $ticket);
    }

    /**
     * Determine if the user can add private/internal comments.
     */
    public function addPrivateComment(User $user, Ticket $ticket): bool
    {
        // Solo el asignado o administradores pueden agregar comentarios privados
        return $user->id === $ticket->assigned_to || $user->isAdmin();
    }

    /**
     * Determine if the user can view private comments.
     */
    public function viewPrivateComments(User $user, Ticket $ticket): bool
    {
        // Solo el asignado o administradores pueden ver comentarios privados
        return $user->id === $ticket->assigned_to || $user->isAdmin();
    }

    /**
     * Determine if the user can rate the ticket (satisfaction survey).
     */
    public function rate(User $user, Ticket $ticket): bool
    {
        // Solo el creador puede valorar el ticket
        // Y solo si está resuelto o cerrado
        return $user->id === $ticket->user_id
            && ($ticket->status === Ticket::STATUS_RESOLVED || $ticket->status === Ticket::STATUS_CLOSED);
    }
}
