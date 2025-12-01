<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'glpi_id',
        'username',
        'firstname',
        'realname',
        'phone',
        'phone2',
        'mobile',
        'glpi_entity_id',
        'entity_name',
        'glpi_location_id',
        'location_name',
        'glpi_profiles',
        'glpi_groups',
        'is_active',
        'last_synced_at',
        'sync_status',
        'tipo_usuario',
        'sucursal',
        'empresa',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'glpi_profiles' => 'array',
            'glpi_groups' => 'array',
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * ==============================================
     * RELACIONES ELOQUENT
     * ==============================================
     */

    /**
     * Tickets reportados por el usuario
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /**
     * Tickets asignados al usuario (como técnico)
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Comentarios creados por el usuario
     */
    public function ticketComments(): HasMany
    {
        return $this->hasMany(TicketComment::class, 'user_id');
    }

    /**
     * Actividades registradas por el usuario
     */
    public function ticketActivities(): HasMany
    {
        return $this->hasMany(TicketActivity::class, 'user_id');
    }

    /**
     * Notificaciones del usuario
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Notificaciones donde el usuario fue quien realizó la acción
     */
    public function actionedNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'action_by');
    }

    /**
     * Tareas creadas por el usuario
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Tareas asignadas al usuario
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Comentarios de tareas creados por el usuario
     */
    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'user_id');
    }

    /**
     * ==============================================
     * SCOPES
     * ==============================================
     */

    /**
     * Scope para obtener solo usuarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para obtener usuarios sincronizados
     */
    public function scopeSynced($query)
    {
        return $query->where('sync_status', 'synced');
    }

    /**
     * Scope para obtener usuarios de GLPI
     */
    public function scopeFromGlpi($query)
    {
        return $query->whereNotNull('glpi_id');
    }

    /**
     * Scope para obtener usuarios finales
     */
    public function scopeUsuariosFinales($query)
    {
        return $query->where('tipo_usuario', 'usuario_final');
    }

    /**
     * Scope para obtener técnicos
     */
    public function scopeTechs($query)
    {
        return $query->where('tipo_usuario', 'tech');
    }

    /**
     * Scope para obtener administradores
     */
    public function scopeAdmins($query)
    {
        return $query->where('tipo_usuario', 'admin');
    }

    /**
     * Verifica si el usuario es administrador en GLPI
     */
    public function isGlpiAdmin(): bool
    {
        if (!$this->glpi_profiles) {
            return false;
        }

        foreach ($this->glpi_profiles as $profile) {
            if (isset($profile['name']) && strtolower($profile['name']) === 'admin') {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica si el usuario es administrador del sistema
     */
    public function isAdmin(): bool
    {
        return $this->tipo_usuario === 'admin';
    }

    /**
     * Verifica si el usuario es técnico
     */
    public function isTech(): bool
    {
        return $this->tipo_usuario === 'tech';
    }

    /**
     * Verifica si el usuario es usuario final
     */
    public function isUsuarioFinal(): bool
    {
        return $this->tipo_usuario === 'usuario_final';
    }

    /**
     * Obtiene el nombre completo del usuario
     */
    public function getFullNameAttribute(): string
    {
        if ($this->firstname && $this->realname) {
            return trim("{$this->firstname} {$this->realname}");
        }

        return $this->name;
    }

    /**
     * Obtiene la etiqueta del tipo de usuario
     */
    public function getTipoUsuarioLabelAttribute(): string
    {
        return match($this->tipo_usuario) {
            'usuario_final' => 'Usuario Final',
            'tech' => 'Técnico',
            'admin' => 'Administrador',
            default => 'N/A',
        };
    }

    /**
     * Obtiene los tipos de usuario disponibles
     */
    public static function getTiposUsuario(): array
    {
        return [
            'usuario_final' => 'Usuario Final',
            'tech' => 'Técnico',
            'admin' => 'Administrador',
        ];
    }
}
