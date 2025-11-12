<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
     * Obtiene el nombre completo del usuario
     */
    public function getFullNameAttribute(): string
    {
        if ($this->firstname && $this->realname) {
            return trim("{$this->firstname} {$this->realname}");
        }

        return $this->name;
    }
}
