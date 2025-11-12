<?php

namespace App\Console\Commands;

use App\Services\Glpi\GlpiService;
use Illuminate\Console\Command;

class TestGlpiConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'glpi:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la conexión con la API de GLPI';

    /**
     * Execute the console command.
     */
    public function handle(GlpiService $glpi)
    {
        $this->info('🔄 Iniciando prueba de conexión con GLPI...');
        $this->newLine();

        $apiUrl = config('services.glpi.api_url');
        $appToken = config('services.glpi.app_token');
        $userToken = config('services.glpi.user_token');

        $this->line("📍 URL: {$apiUrl}");
        $this->line("🔑 App Token: " . substr($appToken, 0, 10) . "...");
        $this->line("👤 User Token: " . substr($userToken, 0, 10) . "...");
        $this->newLine();

        try {
            // Test 1: Iniciar sesión
            $this->info('🔐 Test 1: Iniciando sesión...');
            $sessionToken = $glpi->initSession();
            $this->info('✅ Sesión iniciada correctamente');
            $this->line("  - Session Token: " . substr($sessionToken, 0, 20) . "...");
            $this->newLine();

            // Test 2: Obtener información completa de la sesión
            $this->info('📊 Test 2: Obteniendo información de sesión...');
            $sessionData = $glpi->getFullSession();

            if (isset($sessionData['session']['glpiID'])) {
                $this->line("  - GLPI ID: {$sessionData['session']['glpiID']}");
            }
            if (isset($sessionData['session']['glpiname'])) {
                $this->line("  - Usuario: {$sessionData['session']['glpiname']}");
            }
            if (isset($sessionData['session']['glpirealname'])) {
                $this->line("  - Nombre: {$sessionData['session']['glpirealname']}");
            }
            if (isset($sessionData['session']['glpifirstname'])) {
                $this->line("  - Primer Nombre: {$sessionData['session']['glpifirstname']}");
            }
            $this->newLine();

            // Test 3: Obtener perfiles
            $this->info('👤 Test 3: Obteniendo perfiles de usuario...');
            $profiles = $glpi->getMyProfiles();
            $this->line("  - Total de perfiles: " . count($profiles['myprofiles'] ?? []));
            if (!empty($profiles['myprofiles'])) {
                foreach ($profiles['myprofiles'] as $profile) {
                    $this->line("    • {$profile['name']} (ID: {$profile['id']})");
                }
            }
            $this->newLine();

            // Test 4: Obtener entidades activas
            $this->info('🏢 Test 4: Obteniendo entidades activas...');
            $entities = $glpi->getActiveEntities();
            $this->line("  - Total de entidades: " . count($entities['myentities'] ?? []));
            if (!empty($entities['myentities'])) {
                $entityList = array_slice($entities['myentities'], 0, 5);
                foreach ($entityList as $entity) {
                    $this->line("    • {$entity['name']} (ID: {$entity['id']})");
                }
                if (count($entities['myentities']) > 5) {
                    $remaining = count($entities['myentities']) - 5;
                    $this->line("    ... y {$remaining} más");
                }
            }
            $this->newLine();

            // Test 5: Cerrar sesión
            $this->info('🔒 Test 5: Cerrando sesión...');
            $glpi->killSession();
            $this->info('✅ Sesión cerrada correctamente');
            $this->newLine();

            // Resumen
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('🎉 Todos los tests pasaron exitosamente');
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();
            $this->line('✅ Autenticación');
            $this->line('✅ Obtención de datos de sesión');
            $this->line('✅ Perfiles de usuario');
            $this->line('✅ Entidades activas');
            $this->line('✅ Cierre de sesión');
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Error: ' . $e->getMessage());
            $this->newLine();

            if (str_contains($e->getMessage(), 'configuración')) {
                $this->warn('💡 Asegúrate de que el archivo .env tenga las credenciales correctas');
            } elseif (str_contains($e->getMessage(), 'conexión')) {
                $this->warn('💡 Verifica que la URL de GLPI sea accesible desde este servidor');
            } elseif (str_contains($e->getMessage(), 'autenticación')) {
                $this->warn('💡 Verifica que los tokens sean válidos y tengan permisos');
            }

            return 1;
        }
    }
}
