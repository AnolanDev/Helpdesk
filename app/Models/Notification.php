<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'ticket_id',
        'action_by',
        'action_by_name',
        'url',
        'read',
        'read_at',
        'data',
    ];

    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'icon',
        'color',
        'time_ago',
    ];

    // Tipos de notificaciones
    public const TYPE_TICKET_ASSIGNED = 'ticket_assigned';
    public const TYPE_TICKET_REASSIGNED = 'ticket_reassigned';
    public const TYPE_TICKET_STATUS_CHANGED = 'ticket_status_changed';
    public const TYPE_TICKET_COMMENTED = 'ticket_commented';
    public const TYPE_TICKET_RESOLVED = 'ticket_resolved';
    public const TYPE_TICKET_CLOSED = 'ticket_closed';
    public const TYPE_TICKET_REOPENED = 'ticket_reopened';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            // Cachear nombre del usuario que realizó la acción
            if ($notification->action_by && !$notification->action_by_name) {
                $user = User::find($notification->action_by);
                if ($user) {
                    $notification->action_by_name = $user->name;
                }
            }
        });
    }

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    /**
     * Scopes
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Métodos auxiliares
     */
    public function markAsRead(): void
    {
        $this->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread(): void
    {
        $this->update([
            'read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Accessors
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_TICKET_ASSIGNED => 'user-plus',
            self::TYPE_TICKET_REASSIGNED => 'refresh',
            self::TYPE_TICKET_STATUS_CHANGED => 'clipboard-list',
            self::TYPE_TICKET_COMMENTED => 'chat',
            self::TYPE_TICKET_RESOLVED => 'check-circle',
            self::TYPE_TICKET_CLOSED => 'x-circle',
            self::TYPE_TICKET_REOPENED => 'arrow-circle-up',
            default => 'bell',
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_TICKET_ASSIGNED => 'blue',
            self::TYPE_TICKET_REASSIGNED => 'purple',
            self::TYPE_TICKET_STATUS_CHANGED => 'yellow',
            self::TYPE_TICKET_COMMENTED => 'cyan',
            self::TYPE_TICKET_RESOLVED => 'green',
            self::TYPE_TICKET_CLOSED => 'gray',
            self::TYPE_TICKET_REOPENED => 'orange',
            default => 'gray',
        };
    }

    public function getTimeAgoAttribute(): string
    {
        $diff = (int) $this->created_at->diffInMinutes(now());

        if ($diff < 1) {
            return 'Ahora mismo';
        } elseif ($diff < 60) {
            return "Hace {$diff} min";
        } elseif ($diff < 1440) {
            $hours = floor($diff / 60);
            return "Hace {$hours}h";
        } elseif ($diff < 10080) {
            $days = floor($diff / 1440);
            return "Hace {$days}d";
        } else {
            return $this->created_at->format('d/m/Y');
        }
    }

    /**
     * Métodos estáticos para crear notificaciones
     */
    public static function notifyTicketAssigned(Ticket $ticket, User $assignedTo, User $assignedBy): void
    {
        // Notificar al técnico asignado
        self::create([
            'user_id' => $assignedTo->id,
            'type' => self::TYPE_TICKET_ASSIGNED,
            'title' => 'Nuevo ticket asignado',
            'message' => "Se te ha asignado el ticket #{$ticket->ticket_number}: {$ticket->title}",
            'ticket_id' => $ticket->id,
            'action_by' => $assignedBy->id,
            'url' => route('tickets.show', $ticket->id),
        ]);

        // Notificar al usuario que creó el ticket
        if ($ticket->user_id !== $assignedBy->id) {
            self::create([
                'user_id' => $ticket->user_id,
                'type' => self::TYPE_TICKET_ASSIGNED,
                'title' => 'Tu ticket ha sido asignado',
                'message' => "Tu ticket #{$ticket->ticket_number} ha sido asignado a {$assignedTo->name}",
                'ticket_id' => $ticket->id,
                'action_by' => $assignedBy->id,
                'url' => route('tickets.show', $ticket->id),
            ]);
        }
    }

    public static function notifyTicketReassigned(Ticket $ticket, User $oldAssignee, User $newAssignee, User $reassignedBy): void
    {
        // Notificar al antiguo técnico
        self::create([
            'user_id' => $oldAssignee->id,
            'type' => self::TYPE_TICKET_REASSIGNED,
            'title' => 'Ticket reasignado',
            'message' => "El ticket #{$ticket->ticket_number} ha sido reasignado a {$newAssignee->name}",
            'ticket_id' => $ticket->id,
            'action_by' => $reassignedBy->id,
            'url' => route('tickets.show', $ticket->id),
        ]);

        // Notificar al nuevo técnico
        self::create([
            'user_id' => $newAssignee->id,
            'type' => self::TYPE_TICKET_REASSIGNED,
            'title' => 'Ticket reasignado a ti',
            'message' => "Se te ha reasignado el ticket #{$ticket->ticket_number}: {$ticket->title}",
            'ticket_id' => $ticket->id,
            'action_by' => $reassignedBy->id,
            'url' => route('tickets.show', $ticket->id),
        ]);

        // Notificar al usuario final
        if ($ticket->user_id !== $reassignedBy->id) {
            self::create([
                'user_id' => $ticket->user_id,
                'type' => self::TYPE_TICKET_REASSIGNED,
                'title' => 'Tu ticket ha sido reasignado',
                'message' => "Tu ticket #{$ticket->ticket_number} ahora está asignado a {$newAssignee->name}",
                'ticket_id' => $ticket->id,
                'action_by' => $reassignedBy->id,
                'url' => route('tickets.show', $ticket->id),
            ]);
        }
    }

    public static function notifyTicketStatusChanged(Ticket $ticket, string $oldStatus, string $newStatus, User $changedBy): void
    {
        $statusLabels = Ticket::getStatuses();

        // Notificar al creador del ticket si no es quien hizo el cambio
        if ($ticket->user_id !== $changedBy->id) {
            self::create([
                'user_id' => $ticket->user_id,
                'type' => self::TYPE_TICKET_STATUS_CHANGED,
                'title' => 'Estado del ticket actualizado',
                'message' => "Tu ticket #{$ticket->ticket_number} cambió de '{$statusLabels[$oldStatus]}' a '{$statusLabels[$newStatus]}'",
                'ticket_id' => $ticket->id,
                'action_by' => $changedBy->id,
                'url' => route('tickets.show', $ticket->id),
            ]);
        }

        // Notificar al técnico asignado si existe y no es quien hizo el cambio
        if ($ticket->assigned_to && $ticket->assigned_to !== $changedBy->id) {
            self::create([
                'user_id' => $ticket->assigned_to,
                'type' => self::TYPE_TICKET_STATUS_CHANGED,
                'title' => 'Estado del ticket actualizado',
                'message' => "El ticket #{$ticket->ticket_number} cambió de '{$statusLabels[$oldStatus]}' a '{$statusLabels[$newStatus]}'",
                'ticket_id' => $ticket->id,
                'action_by' => $changedBy->id,
                'url' => route('tickets.show', $ticket->id),
            ]);
        }
    }

    public static function notifyTicketCommented(Ticket $ticket, User $commentedBy): void
    {
        $usersToNotify = [];

        // Notificar al creador si no es quien comentó
        if ($ticket->user_id !== $commentedBy->id) {
            $usersToNotify[] = $ticket->user_id;
        }

        // Notificar al técnico asignado si existe y no es quien comentó
        if ($ticket->assigned_to && $ticket->assigned_to !== $commentedBy->id && !in_array($ticket->assigned_to, $usersToNotify)) {
            $usersToNotify[] = $ticket->assigned_to;
        }

        foreach ($usersToNotify as $userId) {
            self::create([
                'user_id' => $userId,
                'type' => self::TYPE_TICKET_COMMENTED,
                'title' => 'Nuevo comentario en ticket',
                'message' => "{$commentedBy->name} comentó en el ticket #{$ticket->ticket_number}",
                'ticket_id' => $ticket->id,
                'action_by' => $commentedBy->id,
                'url' => route('tickets.show', $ticket->id),
            ]);
        }
    }

    public static function notifyTicketResolved(Ticket $ticket, User $resolvedBy): void
    {
        // Notificar al creador si no es quien resolvió
        if ($ticket->user_id !== $resolvedBy->id) {
            self::create([
                'user_id' => $ticket->user_id,
                'type' => self::TYPE_TICKET_RESOLVED,
                'title' => 'Ticket resuelto',
                'message' => "Tu ticket #{$ticket->ticket_number} ha sido marcado como resuelto",
                'ticket_id' => $ticket->id,
                'action_by' => $resolvedBy->id,
                'url' => route('tickets.show', $ticket->id),
            ]);
        }
    }
}
