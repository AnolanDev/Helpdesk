<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivity extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'user_name',
        'activity_type',
        'description',
        'old_value',
        'new_value',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'icon',
        'color',
        'time_ago',
    ];

    // Activity types
    public const TYPE_CREATED = 'created';
    public const TYPE_ASSIGNED = 'assigned';
    public const TYPE_REASSIGNED = 'reassigned';
    public const TYPE_STATUS_CHANGED = 'status_changed';
    public const TYPE_PRIORITY_CHANGED = 'priority_changed';
    public const TYPE_CATEGORY_CHANGED = 'category_changed';
    public const TYPE_COMMENTED = 'commented';
    public const TYPE_RESOLVED = 'resolved';
    public const TYPE_CLOSED = 'closed';
    public const TYPE_REOPENED = 'reopened';
    public const TYPE_UPDATED = 'updated';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activity) {
            // Cachear nombre del usuario
            if ($activity->user_id && !$activity->user_name) {
                $user = User::find($activity->user_id);
                if ($user) {
                    $activity->user_name = $user->name;
                }
            }
        });
    }

    /**
     * Relaciones
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessors
     */
    public function getIconAttribute(): string
    {
        return match ($this->activity_type) {
            self::TYPE_CREATED => 'plus-circle',
            self::TYPE_ASSIGNED => 'user-plus',
            self::TYPE_REASSIGNED => 'refresh',
            self::TYPE_STATUS_CHANGED => 'clipboard-list',
            self::TYPE_PRIORITY_CHANGED => 'flag',
            self::TYPE_CATEGORY_CHANGED => 'tag',
            self::TYPE_COMMENTED => 'chat',
            self::TYPE_RESOLVED => 'check-circle',
            self::TYPE_CLOSED => 'x-circle',
            self::TYPE_REOPENED => 'arrow-circle-up',
            self::TYPE_UPDATED => 'edit',
            default => 'clock',
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->activity_type) {
            self::TYPE_CREATED => 'blue',
            self::TYPE_ASSIGNED => 'blue',
            self::TYPE_REASSIGNED => 'purple',
            self::TYPE_STATUS_CHANGED => 'yellow',
            self::TYPE_PRIORITY_CHANGED => 'orange',
            self::TYPE_CATEGORY_CHANGED => 'indigo',
            self::TYPE_COMMENTED => 'cyan',
            self::TYPE_RESOLVED => 'green',
            self::TYPE_CLOSED => 'gray',
            self::TYPE_REOPENED => 'orange',
            self::TYPE_UPDATED => 'gray',
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
            return $this->created_at->format('d/m/Y H:i');
        }
    }

    /**
     * Métodos estáticos para registrar actividades
     */
    public static function logCreated(Ticket $ticket, User $user): void
    {
        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_CREATED,
            'description' => "{$user->name} creó el ticket",
            'changes' => [
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category,
            ],
        ]);
    }

    public static function logAssigned(Ticket $ticket, User $assignedTo, User $assignedBy): void
    {
        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $assignedBy->id,
            'activity_type' => self::TYPE_ASSIGNED,
            'description' => "{$assignedBy->name} asignó el ticket a {$assignedTo->name}",
            'new_value' => $assignedTo->name,
        ]);
    }

    public static function logReassigned(Ticket $ticket, User $oldAssignee, User $newAssignee, User $reassignedBy): void
    {
        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $reassignedBy->id,
            'activity_type' => self::TYPE_REASSIGNED,
            'description' => "{$reassignedBy->name} reasignó el ticket de {$oldAssignee->name} a {$newAssignee->name}",
            'old_value' => $oldAssignee->name,
            'new_value' => $newAssignee->name,
        ]);
    }

    public static function logStatusChanged(Ticket $ticket, string $oldStatus, string $newStatus, User $user): void
    {
        $statusLabels = Ticket::getStatuses();

        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_STATUS_CHANGED,
            'description' => "{$user->name} cambió el estado de '{$statusLabels[$oldStatus]}' a '{$statusLabels[$newStatus]}'",
            'old_value' => $statusLabels[$oldStatus],
            'new_value' => $statusLabels[$newStatus],
        ]);
    }

    public static function logPriorityChanged(Ticket $ticket, string $oldPriority, string $newPriority, User $user): void
    {
        $priorityLabels = Ticket::getPriorities();

        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_PRIORITY_CHANGED,
            'description' => "{$user->name} cambió la prioridad de '{$priorityLabels[$oldPriority]}' a '{$priorityLabels[$newPriority]}'",
            'old_value' => $priorityLabels[$oldPriority],
            'new_value' => $priorityLabels[$newPriority],
        ]);
    }

    public static function logCategoryChanged(Ticket $ticket, string $oldCategory, string $newCategory, User $user): void
    {
        $categoryLabels = Ticket::getCategories();

        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_CATEGORY_CHANGED,
            'description' => "{$user->name} cambió la categoría de '{$categoryLabels[$oldCategory]}' a '{$categoryLabels[$newCategory]}'",
            'old_value' => $categoryLabels[$oldCategory],
            'new_value' => $categoryLabels[$newCategory],
        ]);
    }

    public static function logCommented(Ticket $ticket, User $user, string $commentType = 'public'): void
    {
        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_COMMENTED,
            'description' => "{$user->name} agregó un comentario",
            'new_value' => $commentType,
        ]);
    }

    public static function logResolved(Ticket $ticket, User $user): void
    {
        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_RESOLVED,
            'description' => "{$user->name} marcó el ticket como resuelto",
        ]);
    }

    public static function logClosed(Ticket $ticket, User $user): void
    {
        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_CLOSED,
            'description' => "{$user->name} cerró el ticket",
        ]);
    }

    public static function logReopened(Ticket $ticket, User $user): void
    {
        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_REOPENED,
            'description' => "{$user->name} reabrió el ticket",
        ]);
    }

    public static function logUpdated(Ticket $ticket, User $user, array $changes): void
    {
        $changedFields = implode(', ', array_keys($changes));

        self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_UPDATED,
            'description' => "{$user->name} actualizó el ticket ({$changedFields})",
            'changes' => $changes,
        ]);
    }
}
