<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * Cache duration in minutes
     */
    const CACHE_DURATION = 60;

    /**
     * Obtener un valor de configuración
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", self::CACHE_DURATION * 60, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Establecer un valor de configuración
     */
    public static function set(string $key, $value): bool
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );

        // Limpiar caché
        Cache::forget("setting_{$key}");
        Cache::forget('all_settings');

        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }

    /**
     * Obtener todas las configuraciones de un grupo
     */
    public static function getGroup(string $group): array
    {
        return Cache::remember("settings_group_{$group}", self::CACHE_DURATION * 60, function () use ($group) {
            $settings = static::where('group', $group)->get();

            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = static::castValue($setting->value, $setting->type);
            }

            return $result;
        });
    }

    /**
     * Obtener todas las configuraciones
     */
    public static function getAll(): array
    {
        return Cache::remember('all_settings', self::CACHE_DURATION * 60, function () {
            $settings = static::all();

            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = [
                    'value' => static::castValue($setting->value, $setting->type),
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'label' => $setting->label,
                    'description' => $setting->description,
                ];
            }

            return $result;
        });
    }

    /**
     * Limpiar todo el caché de configuraciones
     */
    public static function clearCache(): void
    {
        Cache::flush(); // Alternatively, could be more specific
    }

    /**
     * Convertir el valor al tipo correcto
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'float' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Scopes
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}
