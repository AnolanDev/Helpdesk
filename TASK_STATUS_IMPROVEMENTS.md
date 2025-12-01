# Mejoras del Sistema de Estados de Tareas

## Resumen de Cambios

Esta rama implementa un sistema robusto de gestión de estados para el módulo de tareas con 8 estados bien definidos y validación de transiciones.

## Estados Implementados

### Estados Activos
1. **To Do (todo)** - Pendiente
   - Color: gray
   - Estado inicial de las tareas
   - Transiciones permitidas: Scheduled, In Progress, Cancelled

2. **Scheduled (scheduled)** - Programada
   - Color: purple
   - Tarea programada para ejecutarse en el futuro
   - Transiciones permitidas: Todo, In Progress, Blocked, Cancelled

3. **In Progress (in_progress)** - En Progreso
   - Color: blue
   - Tarea en ejecución activa
   - Registra automáticamente `started_at`
   - Transiciones permitidas: Review, Blocked, Done, Cancelled

4. **Blocked (blocked)** - Bloqueada
   - Color: orange
   - Tarea bloqueada por dependencias u obstáculos
   - Requiere razón del bloqueo (`blocked_reason`)
   - Transiciones permitidas: Todo, Scheduled, In Progress, Cancelled

5. **Review (review)** - En Revisión
   - Color: yellow
   - Tarea esperando revisión
   - Transiciones permitidas: In Progress, Blocked, Done

### Estados Finales
6. **Done (done)** - Completada
   - Color: green
   - Tarea completada exitosamente
   - Registra automáticamente `completed_at`
   - Transiciones permitidas: Archived, In Progress (requiere confirmación)

7. **Cancelled (cancelled)** - Cancelada
   - Color: red
   - Tarea cancelada
   - Transiciones permitidas: Archived, Todo (requiere confirmación)

8. **Archived (archived)** - Archivada
   - Color: slate
   - Tarea archivada para historial
   - Transiciones permitidas: Todo, Done, Cancelled (requieren confirmación)

## Flujo Recomendado de Estados

```
Todo → Scheduled → In Progress → Review → Done → Archived
  ↓                    ↓            ↓
Cancelled          Blocked      Blocked
  ↓                    ↓
Archived            Todo/Scheduled/In Progress
```

### Excepciones
- **Blocked**: Puede aplicarse desde cualquier estado activo
- **Cancelled**: Puede aplicarse desde cualquier estado activo

## Características Implementadas

### 1. Enum TaskStatus (`app/Enums/TaskStatus.php`)
- Define los 8 estados del sistema
- Métodos para obtener labels, colores e íconos
- Validación de transiciones permitidas
- Identificación de estados activos y finales
- Detección de transiciones que requieren confirmación

### 2. Modelo Task Actualizado
**Nuevos campos:**
- `previous_status`: Estado anterior de la tarea
- `status_changed_at`: Fecha del último cambio de estado
- `status_changed_by`: Usuario que realizó el cambio
- `blocked_reason`: Razón del bloqueo (cuando aplica)

**Nuevos métodos:**
- `changeStatus(TaskStatus $newStatus, ?string $reason = null)`: Cambia estado con validación
- `markAsScheduled()`: Marca como programada
- `markAsBlocked(string $reason)`: Bloquea con razón
- `markAsArchived()`: Archiva la tarea
- `unblock()`: Desbloquea una tarea bloqueada
- `isBlocked()`, `isScheduled()`, `isArchived()`: Verificadores de estado

**Validación automática:**
- El modelo valida transiciones en el evento `updating`
- Lanza `InvalidArgumentException` si la transición no es válida
- Registra automáticamente cambios de estado
- Actualiza fechas relevantes (`started_at`, `completed_at`)

### 3. Controlador TaskController Actualizado
**Nuevos endpoints:**
- `PATCH /tasks/{task}/block`: Bloquear tarea con razón
- `PATCH /tasks/{task}/unblock`: Desbloquear tarea
- `PATCH /tasks/{task}/archive`: Archivar tarea
- `GET /tasks/{task}/transitions`: Obtener transiciones permitidas

**Métodos actualizados:**
- `index()`: Incluye stats de todos los estados
- `board()`: Vista Kanban con 5 columnas (Todo, Scheduled, In Progress, Blocked, Review)

### 4. Sistema de Notificaciones
Se crean notificaciones automáticas para:
- Tareas bloqueadas (`task_blocked`)
- Tareas desbloqueadas (`task_unblocked`)
- Cambios de estado existentes actualizados

### 5. Tests Completos (`tests/Feature/TaskStatusTransitionTest.php`)
20 tests que validan:
- ✅ Transiciones permitidas entre estados
- ✅ Transiciones prohibidas (lanzan excepciones)
- ✅ Registro de cambios de estado
- ✅ Bloqueo y desbloqueo de tareas
- ✅ Flujo completo de trabajo
- ✅ Validación de labels y colores del enum
- ✅ Identificación de estados activos/finales
- ✅ Confirmaciones requeridas
- ✅ API de transiciones permitidas

## Reglas de Transición

### Transiciones que Requieren Confirmación
Estas transiciones son permitidas pero el sistema las marca como requiriendo confirmación explícita del usuario:

1. **Done → In Progress**: Reabrir una tarea completada
2. **Cancelled → Todo**: Reactivar una tarea cancelada
3. **Archived → Todo/Done/Cancelled**: Desarchive

### Transiciones Prohibidas
Ejemplos de transiciones que lanzan `InvalidArgumentException`:

- Todo → Done (debe pasar por In Progress)
- Scheduled → Done (debe pasar por In Progress)
- Todo → Review (debe estar en progreso primero)
- Done → Scheduled (incoherente)

## Uso en el Frontend

### Obtener Estados Disponibles
```javascript
// Todos los estados
const statuses = await axios.get('/api/task-statuses')

// Solo estados activos
const activeStatuses = Task::getActiveStatuses()

// Solo estados finales
const finalStatuses = Task::getFinalStatuses()
```

### Obtener Transiciones Permitidas
```javascript
const response = await axios.get(`/tasks/${taskId}/transitions`)
// Retorna:
// {
//   current_status: { value: 'todo', label: 'Pendiente' },
//   allowed_transitions: [
//     { value: 'scheduled', label: 'Programada', color: 'purple', requires_confirmation: false },
//     { value: 'in_progress', label: 'En Progreso', color: 'blue', requires_confirmation: false },
//     { value: 'cancelled', label: 'Cancelada', color: 'red', requires_confirmation: false }
//   ]
// }
```

### Cambiar Estado
```javascript
// Cambio simple
await axios.patch(`/tasks/${taskId}/status`, {
  status: 'in_progress'
})

// Bloquear (requiere razón)
await axios.patch(`/tasks/${taskId}/block`, {
  blocked_reason: 'Esperando aprobación del cliente'
})

// Desbloquear
await axios.patch(`/tasks/${taskId}/unblock`)

// Archivar
await axios.patch(`/tasks/${taskId}/archive`)
```

## Vista Board (Kanban)

La vista de tablero ahora incluye 5 columnas para estados activos:

1. **Pendiente** (Todo)
2. **Programada** (Scheduled)
3. **En Progreso** (In Progress)
4. **Bloqueada** (Blocked)
5. **En Revisión** (Review)

Las tareas pueden arrastrarse entre columnas y el sistema validará automáticamente si la transición es permitida.

## Migraciones

**Archivo**: `database/migrations/2025_12_01_180158_add_new_task_statuses_to_tasks_table.php`

```bash
php artisan migrate
```

Agrega los siguientes campos:
- `previous_status` (string, nullable)
- `status_changed_at` (timestamp, nullable)
- `status_changed_by` (foreign key a users, nullable)
- `blocked_reason` (text, nullable)

## Ejemplos de Código

### Backend - Validación Automática
```php
$task = Task::find(1);

// Esto funciona
$task->markAsInProgress();

// Esto lanza InvalidArgumentException
$task->markAsDone(); // No puede ir de In Progress a Done sin pasar por Review

// Bloquear tarea
$task->markAsBlocked('Esperando recursos del equipo de diseño');

// Desbloquear
$task->unblock(); // Vuelve a Todo automáticamente
```

### Backend - Cambio Manual con Validación
```php
use App\Enums\TaskStatus;

$task = Task::find(1);
$task->changeStatus(TaskStatus::IN_PROGRESS);
// El modelo valida automáticamente si la transición es permitida
```

### Verificar Estado
```php
if ($task->isBlocked()) {
    $reason = $task->blocked_reason;
    // Mostrar mensaje al usuario
}

if ($task->status->isActive()) {
    // La tarea está en un estado activo
}

if ($task->status->isFinal()) {
    // La tarea está completada, cancelada o archivada
}
```

## Beneficios de la Implementación

1. **Consistencia**: Estados estandarizados en todo el sistema
2. **Validación**: Imposible hacer transiciones inválidas
3. **Trazabilidad**: Registro completo de cambios de estado
4. **Flexibilidad**: Sistema de bloqueos y excepciones bien definido
5. **Mantenibilidad**: Código centralizado en el enum
6. **Seguridad**: Type-safe con enums de PHP 8.1+
7. **Testing**: Suite completa de tests automatizados
8. **UX**: Colores e íconos consistentes para cada estado

## Frontend Vue - Implementación Completa

### Componentes Actualizados

#### TaskCard.vue
- ✅ Badge de estado con 8 colores
- ✅ Indicador visual para tareas bloqueadas (borde naranja, fondo naranja claro)
- ✅ Muestra la razón del bloqueo en un alert interno
- ✅ Colores: gray, purple, blue, orange, yellow, green, red, slate

#### Board.vue (Vista Kanban)
- ✅ 5 columnas de estados activos:
  - Pendiente (gray)
  - Programada (purple)
  - En Progreso (blue)
  - Bloqueada (orange)
  - En Revisión (yellow)
- ✅ Drag & drop entre columnas con validación
- ✅ Grid responsive: xl:5 cols, lg:3 cols, md:2 cols
- ✅ Colores de borde distintivos por columna

#### Index.vue (Vista Lista)
- ✅ Stats primarios (5 estados activos)
- ✅ Stats secundarios (Done, Cancelled, Archived, Overdue)
- ✅ Iconos y colores únicos para cada estado
- ✅ Layout responsive 2/3/5 columnas

#### Show.vue (Vista Detalle)
- ✅ Alerta visual para tareas bloqueadas con razón completa
- ✅ Botón "Bloquear Tarea" con modal para ingresar razón
- ✅ Botón "Desbloquear" (solo si está bloqueada)
- ✅ Botón "Archivar" (solo si está Done o Cancelled)
- ✅ Confirmaciones para acciones críticas
- ✅ Soporte completo de 8 colores de estados

## Próximos Pasos Sugeridos

1. ✅ **Frontend Vue**: Completado - Todos los componentes actualizados
2. ✅ **UI/UX**: Completado - Modales y confirmaciones implementadas
3. **Reportes**: Crear reportes de tiempo en cada estado
4. **Automación**: Reglas automáticas (ej: auto-archive después de 30 días en Done)
5. **Webhooks**: Notificaciones externas en cambios de estado
6. **SLA**: Tracking de tiempo límite por estado
7. **Dashboard**: Gráficos de distribución de tareas por estado
8. **Filtros avanzados**: Filtrar por múltiples estados, rangos de fechas

## Archivos Modificados/Creados

### Creados
- `app/Enums/TaskStatus.php` - Enum con los 8 estados y lógica de transiciones
- `database/migrations/2025_12_01_180158_add_new_task_statuses_to_tasks_table.php` - Migración con nuevos campos
- `tests/Feature/TaskStatusTransitionTest.php` - 20 tests completos
- `TASK_STATUS_IMPROVEMENTS.md` - Esta documentación

### Modificados (Backend)
- `app/Models/Task.php` - Integración con enum, validación automática, métodos nuevos
- `app/Http/Controllers/TaskController.php` - Nuevos endpoints (block, unblock, archive, transitions)
- `routes/web.php` - 4 rutas nuevas

### Modificados (Frontend)
- `resources/js/Components/TaskCard.vue` - Soporte 8 estados, indicador de bloqueo
- `resources/js/Pages/Tasks/Board.vue` - 5 columnas Kanban, drag & drop mejorado
- `resources/js/Pages/Tasks/Index.vue` - Stats completos de 8 estados
- `resources/js/Pages/Tasks/Show.vue` - Acciones bloquear/archivar, modal de bloqueo

## Comandos Útiles

```bash
# Ejecutar migración
php artisan migrate

# Ejecutar tests
php artisan test --filter=TaskStatusTransitionTest

# Revertir migración si es necesario
php artisan migrate:rollback --step=1

# Ver todas las rutas de tareas
php artisan route:list --name=tasks
```

## Notas Técnicas

- **PHP Version**: Requiere PHP 8.1+ para soporte de enums
- **Laravel Version**: Compatible con Laravel 10+
- **Base de Datos**: Los campos nuevos son todos nullable para compatibilidad con datos existentes
- **Performance**: Los índices existentes en `status` siguen siendo válidos
- **Backwards Compatibility**: Las constantes antiguas se mantienen en el modelo Task

## Soporte

Para preguntas o issues relacionados con el sistema de estados:
1. Revisar este documento
2. Consultar los tests en `tests/Feature/TaskStatusTransitionTest.php`
3. Ver ejemplos en `app/Enums/TaskStatus.php`
