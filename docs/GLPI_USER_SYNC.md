# Sincronización de Usuarios desde GLPI

Este documento explica cómo funciona la sincronización de usuarios desde GLPI hacia la aplicación Laravel.

## Descripción General

La sincronización de usuarios importa usuarios desde GLPI hacia la base de datos local de Laravel, permitiendo:

- 🎫 Crear tickets asignados a usuarios reales
- 👥 Gestionar permisos y roles localmente
- 📊 Relacionar datos locales con usuarios de GLPI
- ⚡ Mejorar rendimiento (no consultar GLPI constantemente)
- 🔄 Mantener datos actualizados automáticamente

## Campos Sincronizados

### Información Básica
- `glpi_id` - ID único del usuario en GLPI
- `name` - Nombre completo (firstname + realname)
- `username` - Nombre de usuario en GLPI
- `firstname` - Primer nombre
- `realname` - Apellido
- `email` - Correo electrónico

### Contacto
- `phone` - Teléfono principal
- `phone2` - Teléfono secundario
- `mobile` - Teléfono móvil

### Organización
- `glpi_entity_id` - ID de entidad en GLPI
- `entity_name` - Nombre de la entidad
- `glpi_location_id` - ID de ubicación
- `location_name` - Nombre de ubicación

### Permisos y Estado
- `glpi_profiles` - Perfiles del usuario (JSON)
- `glpi_groups` - Grupos del usuario (JSON)
- `is_active` - Usuario activo/inactivo
- `last_synced_at` - Última sincronización
- `sync_status` - Estado: pending, synced, error, deactivated

## Uso del Comando

### Sincronización Completa

```bash
php artisan glpi:sync-users
```

Sincroniza todos los usuarios de GLPI (hasta 9999).

### Opciones Disponibles

#### Limitar cantidad de usuarios
```bash
php artisan glpi:sync-users --limit=50
```
Sincroniza solo los primeros 50 usuarios. Útil para pruebas.

#### Modo Dry-Run (Simulación)
```bash
php artisan glpi:sync-users --dry-run
```
Muestra qué cambios se harían SIN aplicarlos. Perfecto para probar.

#### Combinación de Opciones
```bash
php artisan glpi:sync-users --limit=10 --dry-run
```
Simula sincronización de 10 usuarios.

#### Modo Verbose
```bash
php artisan glpi:sync-users -v
```
Muestra información detallada de errores durante la sincronización.

## Ejemplos de Salida

### Primera Sincronización (Creación)

```
🔄 Iniciando sincronización de usuarios desde GLPI...

📥 Obteniendo usuarios de GLPI...
  ✅ 214 usuarios encontrados

🔄 Procesando usuarios...
██████████████████████████████ 100%

🔍 Verificando usuarios desactivados...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Resumen de Sincronización
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  ✅ Creados:       214
  🔄 Actualizados:  0

  📈 Total procesados: 214
  ⏱️  Duración:        45.3s

✅ Sincronización completada exitosamente
```

### Sincronización Subsecuente (Actualización)

```
🔄 Iniciando sincronización de usuarios desde GLPI...

📥 Obteniendo usuarios de GLPI...
  ✅ 214 usuarios encontrados

🔄 Procesando usuarios...
██████████████████████████████ 100%

🔍 Verificando usuarios desactivados...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Resumen de Sincronización
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  ✅ Creados:       5
  🔄 Actualizados:  209

  📈 Total procesados: 214
  ⏱️  Duración:        38.7s

✅ Sincronización completada exitosamente
```

### Con Usuarios Desactivados

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Resumen de Sincronización
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  ✅ Creados:       10
  🔄 Actualizados:  200
  ⏸️  Desactivados: 4

  📈 Total procesados: 214
  ⏱️  Duración:        42.1s

✅ Sincronización completada exitosamente
```

## Lógica de Sincronización

### 1. Crear Nuevo Usuario

Si el usuario NO existe en la BD local (por `glpi_id`):
- Se crea un nuevo registro
- Se genera contraseña aleatoria segura
- Se marca como `synced`
- `last_synced_at` = ahora

### 2. Actualizar Usuario Existente

Si el usuario YA existe:
- Se actualizan todos sus datos
- Se mantiene su contraseña local
- Se actualiza `last_synced_at`
- Se marca como `synced`

### 3. Desactivar Usuarios Eliminados

Si un usuario existe localmente pero NO en GLPI:
- Se marca como `is_active = false`
- Se marca como `sync_status = deactivated`
- NO se elimina (se mantiene el historial)

## Sincronización Automática

### Configurar Cron (Recomendado para Producción)

Editar `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Sincronizar usuarios cada día a las 2:00 AM
    $schedule->command('glpi:sync-users')->dailyAt('02:00');

    // O cada hora
    $schedule->command('glpi:sync-users')->hourly();

    // O cada 6 horas
    $schedule->command('glpi:sync-users')->everySixHours();
}
```

Luego configurar crontab del servidor:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Usar Usuarios Sincronizados

### En Controladores

```php
use App\Models\User;

// Obtener solo usuarios de GLPI
$glpiUsers = User::fromGlpi()->get();

// Obtener usuarios activos
$activeUsers = User::active()->get();

// Obtener usuarios sincronizados
$syncedUsers = User::synced()->get();

// Usuarios activos de GLPI
$users = User::fromGlpi()->active()->get();

// Buscar por GLPI ID
$user = User::where('glpi_id', 75)->first();

// Verificar si es admin de GLPI
if ($user->isGlpiAdmin()) {
    // Usuario tiene perfil Admin en GLPI
}

// Obtener nombre completo
echo $user->full_name; // "Gustavo Olivera"
```

### En Blade

```blade
@foreach($users as $user)
    <div>
        <strong>{{ $user->full_name }}</strong>
        @if($user->isGlpiAdmin())
            <span class="badge">Admin</span>
        @endif
        <br>
        Email: {{ $user->email }}
        <br>
        Última sincronización: {{ $user->last_synced_at->diffForHumans() }}
    </div>
@endforeach
```

### En Formularios (Select de Usuarios)

```blade
<select name="user_id">
    @foreach(App\Models\User::active()->get() as $user)
        <option value="{{ $user->id }}">
            {{ $user->full_name }} - {{ $user->email }}
        </option>
    @endforeach
</select>
```

## Casos de Uso para HelpTech

### 1. Asignar Ticket a Usuario

```php
use App\Models\Ticket;
use App\Models\User;

$ticket = new Ticket();
$ticket->title = 'Problema con impresora';
$ticket->user_id = User::where('glpi_id', 75)->first()->id;
$ticket->assigned_to = User::where('username', 'tech')->first()->id;
$ticket->save();
```

### 2. Filtrar Tickets por Usuario

```php
// Tickets del usuario actual
$myTickets = Ticket::where('user_id', auth()->id())->get();

// Tickets asignados a técnicos
$techUsers = User::fromGlpi()
    ->whereJsonContains('glpi_profiles', ['name' => 'Technician'])
    ->pluck('id');

$assignedTickets = Ticket::whereIn('assigned_to', $techUsers)->get();
```

### 3. Notificaciones por Email

```php
use Illuminate\Support\Facades\Mail;

// Notificar al usuario sobre su ticket
$user = User::find($ticket->user_id);

Mail::to($user->email)->send(new TicketCreatedMail($ticket));
```

### 4. Dashboard de Usuario

```php
public function dashboard(User $user)
{
    return view('user.dashboard', [
        'user' => $user,
        'openTickets' => $user->tickets()->where('status', 'open')->count(),
        'totalTickets' => $user->tickets()->count(),
        'lastSync' => $user->last_synced_at,
    ]);
}
```

## Manejo de Contraseñas

### Contraseñas NO se sincronizan

Por seguridad, las contraseñas de GLPI NO se sincronizan.

### Opciones de Autenticación

#### 1. Contraseñas Locales (Actual)
- Cada usuario tiene contraseña en Laravel
- Se genera automáticamente (aleatoria)
- Usuario debe resetear contraseña

#### 2. SSO con GLPI (Futuro)
- Usuario inicia sesión en GLPI
- Token se valida con API de GLPI
- No requiere contraseña local

#### 3. Híbrido
- Usuarios pueden usar contraseña local O GLPI
- Mayor flexibilidad

## Troubleshooting

### Error: Duplicate entry for 'email'

**Causa:** Dos usuarios de GLPI con el mismo email.

**Solución:**
```bash
# Ver duplicados
php artisan tinker
User::select('email', DB::raw('count(*) as count'))
    ->groupBy('email')
    ->having('count', '>', 1)
    ->get();

# Corregir en GLPI o manejar con lógica especial
```

### Error: Column 'email' cannot be null

**Causa:** Usuario de GLPI sin email.

**Solución:** El sistema genera email temporal automáticamente:
- `username@noemail.local`
- Actualizar email en GLPI cuando sea posible

### Sincronización muy lenta

**Solución:**
```bash
# Usar límite para chunks
php artisan glpi:sync-users --limit=100

# O ejecutar en background
php artisan glpi:sync-users > /dev/null 2>&1 &
```

### Usuarios no se desactivan

**Causa:** No se está ejecutando la verificación de usuarios eliminados.

**Solución:** Asegurarse de NO usar `--dry-run` en producción.

## Próximos Pasos

Después de sincronizar usuarios, puedes:

1. ✅ **Crear módulo de tickets** - Relacionar tickets con usuarios
2. ✅ **Dashboard de usuarios** - Mostrar estadísticas por usuario
3. ✅ **Sistema de notificaciones** - Email a usuarios
4. ✅ **Control de acceso** - Permisos basados en perfiles GLPI
5. ✅ **Auditoría** - Tracking de acciones por usuario

## Referencias

- [Documentación API GLPI - Users](https://github.com/glpi-project/glpi/blob/master/apirest.md#get-an-item)
- [Laravel Eloquent](https://laravel.com/docs/eloquent)
- [Laravel Task Scheduling](https://laravel.com/docs/scheduling)
