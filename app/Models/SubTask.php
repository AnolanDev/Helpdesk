<?php

namespace App\Models;

use App\Enums\SubTaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'title',
        'status',
        'order',
    ];

    protected $casts = [
        'status' => SubTaskStatus::class,
        'order' => 'integer',
    ];

    protected $appends = [
        'is_completed',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Al crear o actualizar una sub-tarea, recalcular el progreso de la tarea padre
        static::saved(function ($subTask) {
            $subTask->task->updateProgress();
        });

        static::deleted(function ($subTask) {
            $subTask->task->updateProgress();
        });
    }

    /**
     * Relaciones
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Atributos calculados
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === SubTaskStatus::COMPLETADA;
    }

    /**
     * Métodos de acción
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => SubTaskStatus::COMPLETADA]);
    }

    public function markAsPending(): void
    {
        $this->update(['status' => SubTaskStatus::PENDIENTE]);
    }
}
