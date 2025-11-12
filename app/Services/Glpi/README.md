# GLPI Service

Servicio centralizado para interactuar con la API REST de GLPI.

## Configuración

Agregar las siguientes variables al archivo `.env`:

```env
GLPI_API_URL=https://tu-servidor/glpi/apirest.php
GLPI_APP_TOKEN=tu_app_token
GLPI_USER_TOKEN=tu_user_token
GLPI_TIMEOUT=30  # Opcional, por defecto 30 segundos
GLPI_CACHE_TTL=300  # Opcional, por defecto 5 minutos
```

## Uso Básico

### Inyección de Dependencias

```php
use App\Services\Glpi\GlpiService;

class MiControlador extends Controller
{
    public function index(GlpiService $glpi)
    {
        // Usar el servicio
        $usuarios = $glpi->getItems('User');
    }
}
```

### Usando app()

```php
$glpi = app(GlpiService::class);
$computadoras = $glpi->getItems('Computer');
```

## Métodos Disponibles

### Autenticación

```php
// Iniciar sesión (se hace automáticamente)
$sessionToken = $glpi->initSession();

// Cerrar sesión
$glpi->killSession();
```

### Obtener Items

```php
// Obtener un item específico
$usuario = $glpi->getItem('User', 1);

// Obtener múltiples items
$usuarios = $glpi->getItems('User', [
    'range' => '0-50',
    'expand_dropdowns' => true
]);

// Buscar items
$resultados = $glpi->searchItems('Ticket', [
    [
        'field' => 12, // Status
        'searchtype' => 'equals',
        'value' => 1
    ]
]);
```

### Crear, Actualizar y Eliminar

```php
// Crear un item
$nuevoTicket = $glpi->createItem('Ticket', [
    'name' => 'Nuevo ticket',
    'content' => 'Descripción del problema',
    'priority' => 3
]);

// Actualizar un item
$glpi->updateItem('Ticket', 123, [
    'status' => 5,
    'content' => 'Actualización del ticket'
]);

// Eliminar un item (soft delete)
$glpi->deleteItem('Ticket', 123);

// Eliminar permanentemente
$glpi->deleteItem('Ticket', 123, true);
```

### Información de Sesión

```php
// Obtener sesión completa
$session = $glpi->getFullSession();

// Obtener perfiles
$perfiles = $glpi->getMyProfiles();

// Obtener entidades activas
$entidades = $glpi->getActiveEntities();
```

### Peticiones HTTP Directas

```php
// GET
$datos = $glpi->get('User/75');

// POST
$resultado = $glpi->post('Ticket', [
    'input' => [
        'name' => 'Ticket desde API'
    ]
]);

// PUT
$resultado = $glpi->put('Ticket/123', [
    'input' => [
        'status' => 5
    ]
]);

// DELETE
$glpi->delete('Ticket/123');
```

## Tipos de Items Comunes

| Tipo | Descripción |
|------|-------------|
| `User` | Usuarios |
| `Computer` | Computadoras |
| `Ticket` | Tickets |
| `Entity` | Entidades |
| `Group` | Grupos |
| `Profile` | Perfiles |
| `Location` | Ubicaciones |
| `Software` | Software |
| `Monitor` | Monitores |
| `Printer` | Impresoras |
| `NetworkEquipment` | Equipos de red |
| `Phone` | Teléfonos |
| `Project` | Proyectos |
| `ProjectTask` | Tareas de proyecto |

## Manejo de Errores

El servicio lanza excepciones específicas para diferentes tipos de errores:

```php
use App\Exceptions\Glpi\GlpiException;
use App\Exceptions\Glpi\GlpiAuthenticationException;
use App\Exceptions\Glpi\GlpiConnectionException;
use App\Exceptions\Glpi\GlpiConfigurationException;

try {
    $datos = $glpi->getItems('User');
} catch (GlpiAuthenticationException $e) {
    // Error de autenticación
} catch (GlpiConnectionException $e) {
    // Error de conexión
} catch (GlpiConfigurationException $e) {
    // Error de configuración
} catch (GlpiException $e) {
    // Otro error de GLPI
}
```

## Caché

El servicio cachea automáticamente el session token por 5 minutos (configurable).

Si una petición falla por token expirado (401), el servicio automáticamente:
1. Invalida el cache
2. Obtiene un nuevo session token
3. Reintenta la petición

## Ejemplo de Uso en un Comando

```php
use App\Services\Glpi\GlpiService;
use Illuminate\Console\Command;

class SincronizarUsuarios extends Command
{
    protected $signature = 'glpi:sync-users';
    protected $description = 'Sincroniza usuarios desde GLPI';

    public function handle(GlpiService $glpi)
    {
        $this->info('Sincronizando usuarios desde GLPI...');

        try {
            $usuarios = $glpi->getItems('User');

            foreach ($usuarios as $usuario) {
                // Procesar cada usuario
                $this->line("- {$usuario['name']}");
            }

            $this->info('✅ Sincronización completada');

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
```

## Ejemplo de Uso en un Controlador

```php
use App\Services\Glpi\GlpiService;

class ComputadorasController extends Controller
{
    public function index(GlpiService $glpi)
    {
        try {
            $computadoras = $glpi->getItems('Computer', [
                'range' => '0-100',
                'expand_dropdowns' => true
            ]);

            return view('computadoras.index', [
                'computadoras' => $computadoras
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al obtener computadoras: ' . $e->getMessage());
        }
    }

    public function show(GlpiService $glpi, $id)
    {
        try {
            $computadora = $glpi->getItem('Computer', $id);

            return view('computadoras.show', [
                'computadora' => $computadora
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Computadora no encontrada');
        }
    }
}
```

## Testing

Probar la conexión con GLPI:

```bash
php artisan glpi:test
```

Este comando ejecuta 5 tests:
1. ✅ Autenticación
2. ✅ Obtención de datos de sesión
3. ✅ Perfiles de usuario
4. ✅ Entidades activas
5. ✅ Cierre de sesión

## Documentación de la API de GLPI

Para más información sobre los endpoints disponibles, consulta la documentación oficial:

- [Documentación API REST de GLPI](https://github.com/glpi-project/glpi/blob/master/apirest.md)
- [Guía de uso de la API](https://glpi-project.org/documentation/)

## Logging

El servicio registra automáticamente:
- ✅ Sesiones iniciadas
- ✅ Sesiones cerradas
- ❌ Errores de autenticación
- ❌ Errores de conexión

Los logs se encuentran en `storage/logs/laravel.log`
