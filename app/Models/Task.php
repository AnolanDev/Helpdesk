<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_number',
        'title',
        'description',
        'created_by',
        'created_by_name',
        'assigned_to',
        'assigned_to_name',
        'assigned_at',
        'status',
        'previous_status',
        'status_changed_at',
        'status_changed_by',
        'blocked_reason',
        'priority',
        'due_date',
        'completed_at',
        'started_at',
        'empresa',
        'sucursal',
        'department',
        'labels',
        'position',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'previous_status' => TaskStatus::class,
        'assigned_at' => 'datetime',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'labels' => 'array',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'priority_label',
        'priority_color',
        'is_overdue',
    ];

    // Estados del sistema (usando el Enum TaskStatus)
    public const STATUS_RECEIVED = 'received';
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ARCHIVED = 'archived';

    // Prioridades
    public const PRIORITY_LOW = 'baja';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'alta';
    public const PRIORITY_URGENT = 'urgente';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar número de tarea automáticamente
        static::creating(function ($task) {
            if (empty($task->task_number)) {
                $task->task_number = static::generateTaskNumber();
            }

            // Cachear datos del usuario creador
            if ($task->created_by && !$task->created_by_name) {
                $user = User::find($task->created_by);
                if ($user) {
                    $task->created_by_name = $user->name;
                }
            }

            // Cachear datos del usuario asignado
            if ($task->assigned_to && !$task->assigned_to_name) {
                $user = User::find($task->assigned_to);
                if ($user) {
                    $task->assigned_to_name = $user->name;
                }
            }
        });

        // Actualizar fechas según cambios de estado
        static::updating(function ($task) {
            // Validar transiciones de estado
            if ($task->isDirty('status')) {
                $oldStatus = $task->getOriginal('status');
                $newStatus = $task->status;

                // Si oldStatus es string, convertir a enum
                if (is_string($oldStatus)) {
                    $oldStatus = TaskStatus::from($oldStatus);
                }

                // Si newStatus es string, convertir a enum
                if (is_string($newStatus)) {
                    $newStatus = TaskStatus::from($newStatus);
                }

                // Validar que la transición sea permitida
                if (!$oldStatus->canTransitionTo($newStatus)) {
                    throw new \InvalidArgumentException(
                        "No se puede cambiar el estado de '{$oldStatus->label()}' a '{$newStatus->label()}'. " .
                        "Transición no permitida."
                    );
                }

                // Registrar el cambio de estado
                $task->previous_status = $oldStatus;
                $task->status_changed_at = now();
                $task->status_changed_by = auth()->id();

                // Actualizar fechas específicas según el nuevo estado
                if ($newStatus === TaskStatus::IN_PROGRESS && !$task->started_at) {
                    $task->started_at = now();
                }

                if ($newStatus === TaskStatus::DONE && !$task->completed_at) {
                    $task->completed_at = now();
                }

                // Limpiar blocked_reason si ya no está bloqueada
                if ($oldStatus === TaskStatus::BLOCKED && $newStatus !== TaskStatus::BLOCKED) {
                    $task->blocked_reason = null;
                }
            }

            // Si se asigna a alguien
            if ($task->isDirty('assigned_to') && $task->assigned_to) {
                $task->assigned_at = now();
                $assignedUser = User::find($task->assigned_to);
                if ($assignedUser) {
                    $task->assigned_to_name = $assignedUser->name;
                }
            }
        });
    }

    /**
     * Relaciones
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'asc');
    }

    /**
     * Scopes
     */
    public function scopeReceived($query)
    {
        return $query->where('status', TaskStatus::RECEIVED->value);
    }

    public function scopeTodo($query)
    {
        return $query->where('status', TaskStatus::TODO->value);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', TaskStatus::IN_PROGRESS->value);
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', TaskStatus::BLOCKED->value);
    }

    public function scopeDone($query)
    {
        return $query->where('status', TaskStatus::DONE->value);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', TaskStatus::CANCELLED->value);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', TaskStatus::ARCHIVED->value);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            TaskStatus::RECEIVED->value,
            TaskStatus::TODO->value,
            TaskStatus::IN_PROGRESS->value,
            TaskStatus::BLOCKED->value,
        ]);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', [
                TaskStatus::DONE->value,
                TaskStatus::CANCELLED->value,
                TaskStatus::ARCHIVED->value,
            ]);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Métodos auxiliares
     */
    public static function generateTaskNumber(): string
    {
        $fecha = date('Ymd'); // YYYYMMDD

        // Obtener todas las tareas del día
        $tasks = static::where('task_number', 'like', 'TSK-' . $fecha . '-%')
            ->withTrashed()
            ->pluck('task_number');

        $maxConsecutivo = 0;

        foreach ($tasks as $taskNumber) {
            $parts = explode('-', $taskNumber);
            if (count($parts) === 3 && is_numeric($parts[2])) {
                $numero = (int) $parts[2];
                if ($numero > $maxConsecutivo) {
                    $maxConsecutivo = $numero;
                }
            }
        }

        $consecutivo = $maxConsecutivo + 1;

        return sprintf('TSK-%s-%04d', $fecha, $consecutivo);
    }

    public function isActive(): bool
    {
        if (is_string($this->status)) {
            $status = TaskStatus::from($this->status);
        } else {
            $status = $this->status;
        }
        return $status->isActive();
    }

    public function isDone(): bool
    {
        if (is_string($this->status)) {
            return $this->status === TaskStatus::DONE->value;
        }
        return $this->status === TaskStatus::DONE;
    }

    public function isCancelled(): bool
    {
        if (is_string($this->status)) {
            return $this->status === TaskStatus::CANCELLED->value;
        }
        return $this->status === TaskStatus::CANCELLED;
    }

    public function isArchived(): bool
    {
        if (is_string($this->status)) {
            return $this->status === TaskStatus::ARCHIVED->value;
        }
        return $this->status === TaskStatus::ARCHIVED;
    }

    public function isBlocked(): bool
    {
        if (is_string($this->status)) {
            return $this->status === TaskStatus::BLOCKED->value;
        }
        return $this->status === TaskStatus::BLOCKED;
    }

    public function isReceived(): bool
    {
        if (is_string($this->status)) {
            return $this->status === TaskStatus::RECEIVED->value;
        }
        return $this->status === TaskStatus::RECEIVED;
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date < now() && !$this->isDone() && !$this->isCancelled();
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->isOverdue();
    }

    public function getStatusLabelAttribute(): string
    {
        if (is_string($this->status)) {
            return TaskStatus::from($this->status)->label();
        }
        return $this->status->label();
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            static::PRIORITY_LOW => 'Baja',
            static::PRIORITY_NORMAL => 'Normal',
            static::PRIORITY_HIGH => 'Alta',
            static::PRIORITY_URGENT => 'Urgente',
            default => $this->priority,
        };
    }

    public function getStatusColorAttribute(): string
    {
        if (is_string($this->status)) {
            return TaskStatus::from($this->status)->color();
        }
        return $this->status->color();
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            static::PRIORITY_LOW => 'gray',
            static::PRIORITY_NORMAL => 'blue',
            static::PRIORITY_HIGH => 'orange',
            static::PRIORITY_URGENT => 'red',
            default => 'gray',
        };
    }

    /**
     * Acciones sobre la tarea
     */
    public function assignTo(User $user): void
    {
        $this->update([
            'assigned_to' => $user->id,
            'assigned_to_name' => $user->name,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Cambiar el estado de la tarea validando transiciones
     */
    public function changeStatus(TaskStatus $newStatus, ?string $reason = null): void
    {
        $currentStatus = is_string($this->status) ? TaskStatus::from($this->status) : $this->status;

        if (!$currentStatus->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                "No se puede cambiar el estado de '{$currentStatus->label()}' a '{$newStatus->label()}'. " .
                "Transición no permitida."
            );
        }

        $updateData = ['status' => $newStatus];

        // Si se bloquea, guardar la razón
        if ($newStatus === TaskStatus::BLOCKED && $reason) {
            $updateData['blocked_reason'] = $reason;
        }

        $this->update($updateData);
    }

    public function markAsInProgress(): void
    {
        $this->changeStatus(TaskStatus::IN_PROGRESS);
    }

    public function markAsBlocked(string $reason): void
    {
        $this->changeStatus(TaskStatus::BLOCKED, $reason);
    }

    public function markAsDone(): void
    {
        $this->changeStatus(TaskStatus::DONE);
    }

    public function markAsCancelled(): void
    {
        $this->changeStatus(TaskStatus::CANCELLED);
    }

    public function markAsArchived(): void
    {
        $this->changeStatus(TaskStatus::ARCHIVED);
    }

    public function unblock(): void
    {
        if (!$this->isBlocked()) {
            throw new \InvalidArgumentException('La tarea no está bloqueada.');
        }

        $this->update([
            'status' => TaskStatus::TODO,
            'blocked_reason' => null,
        ]);
    }

    public function addComment(string $comment, string $type = 'comment'): TaskComment
    {
        return $this->comments()->create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'comment' => $comment,
            'type' => $type,
        ]);
    }

    /**
     * Métodos estáticos para obtener opciones
     */
    public static function getStatuses(): array
    {
        return TaskStatus::toArray();
    }

    public static function getActiveStatuses(): array
    {
        $statuses = [];
        foreach (TaskStatus::activeStatuses() as $status) {
            $statuses[$status->value] = $status->label();
        }
        return $statuses;
    }

    public static function getFinalStatuses(): array
    {
        $statuses = [];
        foreach (TaskStatus::finalStatuses() as $status) {
            $statuses[$status->value] = $status->label();
        }
        return $statuses;
    }

    public static function getPriorities(): array
    {
        return [
            static::PRIORITY_LOW => 'Baja',
            static::PRIORITY_NORMAL => 'Normal',
            static::PRIORITY_HIGH => 'Alta',
            static::PRIORITY_URGENT => 'Urgente',
        ];
    }

    public static function getEmpresas(): array
    {
        return [
            'Asercol',
            'Sotracar',
            'Ci Global Services',
            'Ambientados',
        ];
    }
}
