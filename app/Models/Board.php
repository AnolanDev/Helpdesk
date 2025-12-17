<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Board extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'tasks_count',
        'completed_tasks_count',
        'progress_percentage',
    ];

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function sharedWith(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'board_user')
            ->withPivot('permission')
            ->withTimestamps();
    }

    /**
     * Métodos de permisos
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function isSharedWith(User $user): bool
    {
        return $this->sharedWith()->where('user_id', $user->id)->exists();
    }

    public function getPermissionFor(User $user): ?string
    {
        if ($this->isOwnedBy($user)) {
            return 'write'; // El propietario siempre tiene permisos de escritura
        }

        $shared = $this->sharedWith()->where('user_id', $user->id)->first();
        return $shared?->pivot->permission;
    }

    public function hasWritePermission(User $user): bool
    {
        return $this->getPermissionFor($user) === 'write';
    }

    public function hasReadPermission(User $user): bool
    {
        return in_array($this->getPermissionFor($user), ['read', 'write']);
    }

    public function shareWith(User $user, string $permission = 'read'): void
    {
        $this->sharedWith()->syncWithoutDetaching([
            $user->id => ['permission' => $permission]
        ]);
    }

    public function unshareWith(User $user): void
    {
        $this->sharedWith()->detach($user->id);
    }

    public function updatePermissionFor(User $user, string $permission): void
    {
        $this->sharedWith()->updateExistingPivot($user->id, [
            'permission' => $permission
        ]);
    }

    /**
     * Scopes
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->orWhereHas('sharedWith', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
    }

    /**
     * Atributos calculados
     */
    public function getTasksCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    public function getCompletedTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', 'finalizada')->count();
    }

    public function getProgressPercentageAttribute(): int
    {
        $total = $this->tasks_count;
        if ($total === 0) {
            return 0;
        }
        return (int) (($this->completed_tasks_count / $total) * 100);
    }
}
