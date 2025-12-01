<?php

namespace App\Models;

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
        'assigned_at' => 'datetime',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'labels' => 'array',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'priority_label',
        'priority_color',
        'is_overdue',
    ];

    // Estados tipo Trello
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_REVIEW = 'review';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

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
            // Si se marca como en progreso
            if ($task->isDirty('status') && $task->status === static::STATUS_IN_PROGRESS && !$task->started_at) {
                $task->started_at = now();
            }

            // Si se marca como completada
            if ($task->isDirty('status') && $task->status === static::STATUS_DONE && !$task->completed_at) {
                $task->completed_at = now();
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
    public function scopeTodo($query)
    {
        return $query->where('status', static::STATUS_TODO);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', static::STATUS_IN_PROGRESS);
    }

    public function scopeInReview($query)
    {
        return $query->where('status', static::STATUS_REVIEW);
    }

    public function scopeDone($query)
    {
        return $query->where('status', static::STATUS_DONE);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            static::STATUS_TODO,
            static::STATUS_IN_PROGRESS,
            static::STATUS_REVIEW,
        ]);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', [static::STATUS_DONE, static::STATUS_CANCELLED]);
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
        return in_array($this->status, [
            static::STATUS_TODO,
            static::STATUS_IN_PROGRESS,
            static::STATUS_REVIEW,
        ]);
    }

    public function isDone(): bool
    {
        return $this->status === static::STATUS_DONE;
    }

    public function isCancelled(): bool
    {
        return $this->status === static::STATUS_CANCELLED;
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
        return match ($this->status) {
            static::STATUS_TODO => 'Por Hacer',
            static::STATUS_IN_PROGRESS => 'En Progreso',
            static::STATUS_REVIEW => 'En Revisión',
            static::STATUS_DONE => 'Completada',
            static::STATUS_CANCELLED => 'Cancelada',
            default => $this->status,
        };
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
        return match ($this->status) {
            static::STATUS_TODO => 'gray',
            static::STATUS_IN_PROGRESS => 'blue',
            static::STATUS_REVIEW => 'yellow',
            static::STATUS_DONE => 'green',
            static::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
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

    public function markAsInProgress(): void
    {
        $this->update([
            'status' => static::STATUS_IN_PROGRESS,
            'started_at' => $this->started_at ?? now(),
        ]);
    }

    public function markAsInReview(): void
    {
        $this->update([
            'status' => static::STATUS_REVIEW,
        ]);
    }

    public function markAsDone(): void
    {
        $this->update([
            'status' => static::STATUS_DONE,
            'completed_at' => now(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => static::STATUS_CANCELLED,
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
        return [
            static::STATUS_TODO => 'Por Hacer',
            static::STATUS_IN_PROGRESS => 'En Progreso',
            static::STATUS_REVIEW => 'En Revisión',
            static::STATUS_DONE => 'Completada',
            static::STATUS_CANCELLED => 'Cancelada',
        ];
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
