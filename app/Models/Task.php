<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'board_id',
        'title',
        'description',
        'status',
        'priority',
        'cancel_reason',
        'completed_at',
        'progress',
        'is_overdue',
        'overdue_notified_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
        'completed_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
        'progress' => 'integer',
        'is_overdue' => 'boolean',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'priority_label',
        'priority_color',
        'time_status',
        'time_color',
        'time_percentage',
        'hours_elapsed',
        'hours_remaining',
        'target_hours',
        'can_be_finalized',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Al crear una tarea, establecer estado CREADA
        static::creating(function ($task) {
            if (empty($task->status)) {
                $task->status = TaskStatus::CREADA;
            }
            if (empty($task->priority)) {
                $task->priority = TaskPriority::NORMAL;
            }
        });

        // Al actualizar el estado
        static::updating(function ($task) {
            if ($task->isDirty('status')) {
                $oldStatus = $task->getOriginal('status');
                $newStatus = $task->status;

                // Convertir a enum si es string
                if (is_string($oldStatus)) {
                    $oldStatus = TaskStatus::from($oldStatus);
                }
                if (is_string($newStatus)) {
                    $newStatus = TaskStatus::from($newStatus);
                }

                // Validar transición
                if (!$oldStatus->canTransitionTo($newStatus)) {
                    throw new \InvalidArgumentException(
                        "No se puede cambiar el estado de '{$oldStatus->label()}' a '{$newStatus->label()}'."
                    );
                }

                // Si se finaliza, guardar fecha
                if ($newStatus === TaskStatus::FINALIZADA && !$task->completed_at) {
                    $task->completed_at = now();
                }

                // Si se cancela, validar que tenga razón
                if ($newStatus === TaskStatus::CANCELADA && empty($task->cancel_reason)) {
                    throw new \InvalidArgumentException('Debe proporcionar una razón para cancelar la tarea.');
                }
            }
        });
    }

    /**
     * Relaciones
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function subTasks(): HasMany
    {
        return $this->hasMany(SubTask::class)->orderBy('order');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByBoard($query, $boardId)
    {
        return $query->where('board_id', $boardId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            TaskStatus::FINALIZADA->value,
            TaskStatus::CANCELADA->value,
        ]);
    }

    /**
     * Atributos calculados
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }

    public function getPriorityLabelAttribute(): string
    {
        return $this->priority->label();
    }

    public function getPriorityColorAttribute(): string
    {
        return $this->priority->color();
    }

    public function getTargetHoursAttribute(): float
    {
        return $this->priority->targetHours();
    }

    public function getHoursElapsedAttribute(): float
    {
        if ($this->completed_at) {
            return $this->created_at->diffInHours($this->completed_at, true);
        }
        return $this->created_at->diffInHours(now(), true);
    }

    public function getHoursRemainingAttribute(): float
    {
        $target = $this->target_hours;
        $elapsed = $this->hours_elapsed;
        return max(0, $target - $elapsed);
    }

    public function getTimePercentageAttribute(): float
    {
        $target = $this->target_hours;
        if ($target == 0) {
            return 100; // Urgente siempre al 100%
        }
        $elapsed = $this->hours_elapsed;
        return min(100, ($elapsed / $target) * 100);
    }

    public function getTimeStatusAttribute(): string
    {
        $percentage = $this->time_percentage;

        if ($percentage <= 50) {
            return 'within_time';
        } elseif ($percentage <= 100) {
            return 'warning';
        } else {
            return 'overdue';
        }
    }

    public function getTimeColorAttribute(): string
    {
        return match ($this->time_status) {
            'within_time' => 'green',
            'warning' => 'yellow',
            'overdue' => 'red',
            default => 'gray',
        };
    }

    public function getCanBeFinalizedAttribute(): bool
    {
        // Solo se puede finalizar si todas las sub-tareas están completadas
        if ($this->subTasks()->count() === 0) {
            return true;
        }
        return $this->subTasks()->where('status', 'pendiente')->count() === 0;
    }

    /**
     * Métodos de acción
     */
    public function updateProgress(): void
    {
        $total = $this->subTasks()->count();
        if ($total === 0) {
            $this->update(['progress' => 0]);
            return;
        }

        $completed = $this->subTasks()->where('status', 'completada')->count();
        $percentage = (int) (($completed / $total) * 100);
        $this->update(['progress' => $percentage]);
    }

    public function checkAndUpdateOverdueStatus(): void
    {
        $isOverdue = $this->time_status === 'overdue';

        // Si cambió el estado de vencimiento
        if ($isOverdue && !$this->is_overdue) {
            $this->update([
                'is_overdue' => true,
                'overdue_notified_at' => null, // Reset para volver a notificar
            ]);
        } elseif (!$isOverdue && $this->is_overdue) {
            $this->update(['is_overdue' => false]);
        }
    }

    public function markAsOverdueNotified(): void
    {
        $this->update(['overdue_notified_at' => now()]);
    }

    public function changeStatus(TaskStatus $newStatus, ?string $cancelReason = null): void
    {
        $updateData = ['status' => $newStatus];

        if ($newStatus === TaskStatus::CANCELADA) {
            if (empty($cancelReason)) {
                throw new \InvalidArgumentException('Debe proporcionar una razón para cancelar la tarea.');
            }
            $updateData['cancel_reason'] = $cancelReason;
        }

        $this->update($updateData);
    }

    /**
     * Métodos estáticos
     */
    public static function getStatuses(): array
    {
        return TaskStatus::toArray();
    }

    public static function getPriorities(): array
    {
        return TaskPriority::toArray();
    }
}
