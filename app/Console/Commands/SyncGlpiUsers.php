<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Glpi\GlpiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncGlpiUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'glpi:sync-users
                            {--limit= : Limitar el número de usuarios a sincronizar}
                            {--force : Forzar sincronización de todos los usuarios}
                            {--dry-run : Mostrar cambios sin aplicarlos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza usuarios desde GLPI hacia la base de datos local';

    protected int $created = 0;
    protected int $updated = 0;
    protected int $deactivated = 0;
    protected int $errors = 0;
    protected int $skipped = 0;

    /**
     * Execute the console command.
     */
    public function handle(GlpiService $glpi)
    {
        $startTime = microtime(true);

        $this->info('🔄 Iniciando sincronización de usuarios desde GLPI...');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('⚠️  Modo DRY-RUN activado - No se aplicarán cambios');
            $this->newLine();
        }

        try {
            // Paso 1: Obtener usuarios de GLPI
            $this->line('📥 Obteniendo usuarios de GLPI...');
            $glpiUsers = $this->getGlpiUsers($glpi);
            $totalUsers = count($glpiUsers);
            $this->info("  ✅ {$totalUsers} usuarios encontrados");
            $this->newLine();

            // Aplicar límite si se especifica
            if ($limit = $this->option('limit')) {
                $glpiUsers = array_slice($glpiUsers, 0, (int) $limit);
                $this->warn("  ⚠️  Limitado a {$limit} usuarios");
                $this->newLine();
            }

            // Paso 2: Procesar usuarios
            $this->line('🔄 Procesando usuarios...');
            $progressBar = $this->output->createProgressBar(count($glpiUsers));
            $progressBar->start();

            foreach ($glpiUsers as $glpiUser) {
                $this->syncUser($glpiUser, $glpi);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            // Paso 3: Desactivar usuarios que ya no existen en GLPI
            if (!$this->option('dry-run')) {
                $this->line('🔍 Verificando usuarios desactivados...');
                $this->deactivateRemovedUsers($glpiUsers);
                $this->newLine();
            }

            // Resumen
            $duration = round(microtime(true) - $startTime, 2);
            $this->showSummary($duration);

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Error durante la sincronización:');
            $this->error($e->getMessage());
            $this->newLine();

            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return 1;
        }
    }

    /**
     * Obtiene todos los usuarios de GLPI
     */
    protected function getGlpiUsers(GlpiService $glpi): array
    {
        try {
            $users = $glpi->getItems('User', [
                'range' => '0-9999',
                'expand_dropdowns' => false,
            ]);

            return $users;

        } catch (\Exception $e) {
            $this->error('Error al obtener usuarios de GLPI: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sincroniza un usuario individual
     */
    protected function syncUser(array $glpiUser, GlpiService $glpi): void
    {
        try {
            // Validar datos mínimos
            if (empty($glpiUser['id'])) {
                $this->skipped++;
                return;
            }

            // Buscar usuario existente por glpi_id
            $user = User::where('glpi_id', $glpiUser['id'])->first();

            // Preparar datos del usuario
            $userData = $this->prepareUserData($glpiUser, $glpi);

            if ($this->option('dry-run')) {
                if ($user) {
                    $this->line("  [DRY-RUN] Actualizaría: {$userData['name']}");
                } else {
                    $this->line("  [DRY-RUN] Crearía: {$userData['name']}");
                }
                return;
            }

            DB::beginTransaction();

            try {
                if ($user) {
                    // Actualizar usuario existente
                    $user->update($userData);
                    $this->updated++;
                } else {
                    // Crear nuevo usuario
                    User::create($userData);
                    $this->created++;
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors++;
                if ($this->output->isVerbose()) {
                    $this->warn("  ⚠️  Error con usuario {$glpiUser['name']}: {$e->getMessage()}");
                }
            }

        } catch (\Exception $e) {
            $this->errors++;
            if ($this->output->isVerbose()) {
                $this->warn("  ⚠️  Error procesando usuario: {$e->getMessage()}");
            }
        }
    }

    /**
     * Prepara los datos del usuario para insertar/actualizar
     */
    protected function prepareUserData(array $glpiUser, GlpiService $glpi): array
    {
        // Obtener email (puede estar en diferentes campos)
        $email = $this->extractEmail($glpiUser);

        // Construir nombre completo
        $fullName = trim(($glpiUser['firstname'] ?? '') . ' ' . ($glpiUser['realname'] ?? ''));
        if (empty($fullName)) {
            $fullName = $glpiUser['name'] ?? 'Usuario ' . $glpiUser['id'];
        }

        // Obtener perfiles del usuario
        $profiles = $this->getUserProfiles($glpi, $glpiUser['id']);

        return [
            'glpi_id' => $glpiUser['id'],
            'name' => $fullName,
            'username' => $glpiUser['name'] ?? null,
            'firstname' => $glpiUser['firstname'] ?? null,
            'realname' => $glpiUser['realname'] ?? null,
            'email' => $email,
            'phone' => $glpiUser['phone'] ?? null,
            'phone2' => $glpiUser['phone2'] ?? null,
            'mobile' => $glpiUser['mobile'] ?? null,
            'glpi_entity_id' => $glpiUser['entities_id'] ?? null,
            'glpi_location_id' => $glpiUser['locations_id'] ?? null,
            'glpi_profiles' => $profiles,
            'is_active' => isset($glpiUser['is_active']) ? (bool) $glpiUser['is_active'] : true,
            'last_synced_at' => now(),
            'sync_status' => 'synced',
            'password' => $this->generateSecurePassword(),
        ];
    }

    /**
     * Extrae el email del usuario de GLPI
     */
    protected function extractEmail(array $glpiUser): string
    {
        // Intentar diferentes campos donde puede estar el email
        $email = $glpiUser['email'] ??
                 $glpiUser['_useremails'] ??
                 $glpiUser['default_email'] ??
                 null;

        // Si no hay email, generar uno temporal
        if (empty($email)) {
            $username = $glpiUser['name'] ?? 'user' . $glpiUser['id'];
            $email = strtolower($username) . '@noemail.local';
        }

        return $email;
    }

    /**
     * Obtiene los perfiles del usuario desde GLPI
     */
    protected function getUserProfiles(GlpiService $glpi, int $userId): ?array
    {
        try {
            $profiles = $glpi->get("User/{$userId}/Profile_User");
            return $profiles;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Desactiva usuarios que ya no existen en GLPI
     */
    protected function deactivateRemovedUsers(array $glpiUsers): void
    {
        $glpiUserIds = collect($glpiUsers)->pluck('id')->toArray();

        $usersToDeactivate = User::whereNotNull('glpi_id')
            ->whereNotIn('glpi_id', $glpiUserIds)
            ->where('is_active', true)
            ->get();

        foreach ($usersToDeactivate as $user) {
            $user->update([
                'is_active' => false,
                'sync_status' => 'deactivated',
            ]);
            $this->deactivated++;
        }

        if ($this->deactivated > 0) {
            $this->info("  ✅ {$this->deactivated} usuarios desactivados");
        }
    }

    /**
     * Genera una contraseña segura aleatoria
     */
    protected function generateSecurePassword(): string
    {
        return Hash::make(Str::random(32));
    }

    /**
     * Muestra el resumen de la sincronización
     */
    protected function showSummary(float $duration): void
    {
        $total = $this->created + $this->updated + $this->deactivated + $this->errors + $this->skipped;

        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 Resumen de Sincronización');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->line("  <fg=green>✅ Creados:</>       {$this->created}");
        $this->line("  <fg=blue>🔄 Actualizados:</>  {$this->updated}");

        if ($this->deactivated > 0) {
            $this->line("  <fg=yellow>⏸️  Desactivados:</> {$this->deactivated}");
        }

        if ($this->skipped > 0) {
            $this->line("  <fg=gray>⏭️  Omitidos:</>     {$this->skipped}");
        }

        if ($this->errors > 0) {
            $this->line("  <fg=red>❌ Errores:</>      {$this->errors}");
        }

        $this->newLine();
        $this->line("  <fg=cyan>📈 Total procesados:</> {$total}");
        $this->line("  <fg=cyan>⏱️  Duración:</>        {$duration}s");
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('⚠️  Modo DRY-RUN - Ningún cambio fue aplicado');
        } else {
            $this->info('✅ Sincronización completada exitosamente');
        }

        $this->newLine();
    }
}
