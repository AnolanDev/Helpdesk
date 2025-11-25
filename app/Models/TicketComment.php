<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'user_name',
        'comment',
        'type',
        'is_private',
        'attachments',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'attachments' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'type_label',
    ];

    // Tipos de comentarios
    public const TYPE_PUBLIC = 'public';
    public const TYPE_INTERNAL = 'internal';
    public const TYPE_SOLUTION = 'solution';
    public const TYPE_STATUS_CHANGE = 'status_change';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Cachear nombre del usuario automáticamente
        static::creating(function ($comment) {
            if ($comment->user_id && !$comment->user_name) {
                $user = User::find($comment->user_id);
                if ($user) {
                    $comment->user_name = $user->name;
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
     * Scopes
     */
    public function scopePublic($query)
    {
        return $query->where('type', static::TYPE_PUBLIC)
            ->where('is_private', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('type', static::TYPE_INTERNAL)
            ->orWhere('is_private', true);
    }

    public function scopeSolutions($query)
    {
        return $query->where('type', static::TYPE_SOLUTION);
    }

    public function scopeStatusChanges($query)
    {
        return $query->where('type', static::TYPE_STATUS_CHANGE);
    }

    /**
     * Métodos auxiliares
     */
    public function isPublic(): bool
    {
        return $this->type === static::TYPE_PUBLIC && !$this->is_private;
    }

    public function isInternal(): bool
    {
        return $this->type === static::TYPE_INTERNAL || $this->is_private;
    }

    public function isSolution(): bool
    {
        return $this->type === static::TYPE_SOLUTION;
    }

    public function isStatusChange(): bool
    {
        return $this->type === static::TYPE_STATUS_CHANGE;
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            static::TYPE_PUBLIC => 'Público',
            static::TYPE_INTERNAL => 'Interno',
            static::TYPE_SOLUTION => 'Solución',
            static::TYPE_STATUS_CHANGE => 'Cambio de Estado',
            default => $this->type,
        };
    }

    /**
     * Métodos estáticos para obtener opciones
     */
    public static function getTypes(): array
    {
        return [
            static::TYPE_PUBLIC => 'Público',
            static::TYPE_INTERNAL => 'Interno',
            static::TYPE_SOLUTION => 'Solución',
            static::TYPE_STATUS_CHANGE => 'Cambio de Estado',
        ];
    }
}
