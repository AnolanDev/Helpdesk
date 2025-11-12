<?php

namespace App\Services\Glpi;

use App\Exceptions\Glpi\GlpiAuthenticationException;
use App\Exceptions\Glpi\GlpiConfigurationException;
use App\Exceptions\Glpi\GlpiConnectionException;
use App\Exceptions\Glpi\GlpiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GlpiService
{
    protected string $apiUrl;
    protected string $appToken;
    protected string $userToken;
    protected int $timeout;
    protected int $cacheTtl;
    protected ?string $sessionToken = null;

    public function __construct()
    {
        $this->apiUrl = config('services.glpi.api_url');
        $this->appToken = config('services.glpi.app_token');
        $this->userToken = config('services.glpi.user_token');
        $this->timeout = config('services.glpi.timeout', 30);
        $this->cacheTtl = config('services.glpi.cache_ttl', 300);

        $this->validateConfiguration();
    }

    /**
     * Valida que la configuración necesaria esté presente
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->apiUrl) || empty($this->appToken) || empty($this->userToken)) {
            throw new GlpiConfigurationException(
                'Faltan credenciales de GLPI. Verifica GLPI_API_URL, GLPI_APP_TOKEN y GLPI_USER_TOKEN en .env'
            );
        }
    }

    /**
     * Inicia sesión en GLPI y obtiene el session token
     */
    public function initSession(): string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'App-Token' => $this->appToken,
                    'Authorization' => "user_token {$this->userToken}",
                ])
                ->get("{$this->apiUrl}/initSession");

            if (!$response->successful()) {
                Log::error('GLPI Authentication failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new GlpiAuthenticationException(
                    "Error de autenticación con GLPI: {$response->status()}"
                );
            }

            $data = $response->json();
            $this->sessionToken = $data['session_token'] ?? null;

            if (!$this->sessionToken) {
                throw new GlpiAuthenticationException('No se recibió session token de GLPI');
            }

            // Cachear el session token
            Cache::put('glpi_session_token', $this->sessionToken, now()->addMinutes(5));

            Log::info('GLPI Session initiated', [
                'user' => $data['glpiname'] ?? 'unknown',
            ]);

            return $this->sessionToken;

        } catch (GlpiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('GLPI Connection error', [
                'message' => $e->getMessage(),
            ]);

            throw new GlpiConnectionException(
                "Error de conexión con GLPI: {$e->getMessage()}"
            );
        }
    }

    /**
     * Cierra la sesión actual en GLPI
     */
    public function killSession(?string $sessionToken = null): bool
    {
        $token = $sessionToken ?? $this->sessionToken ?? Cache::get('glpi_session_token');

        if (!$token) {
            return true; // No hay sesión que cerrar
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'App-Token' => $this->appToken,
                    'Session-Token' => $token,
                ])
                ->get("{$this->apiUrl}/killSession");

            // Limpiar cache
            Cache::forget('glpi_session_token');
            $this->sessionToken = null;

            Log::info('GLPI Session killed');

            return $response->successful();

        } catch (\Exception $e) {
            Log::warning('Error killing GLPI session', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Obtiene o crea un session token válido
     */
    protected function getSessionToken(): string
    {
        // Intentar obtener del cache
        $cachedToken = Cache::get('glpi_session_token');
        if ($cachedToken) {
            $this->sessionToken = $cachedToken;
            return $cachedToken;
        }

        // Si no hay en cache, iniciar nueva sesión
        return $this->initSession();
    }

    /**
     * Realiza una petición GET a la API de GLPI
     */
    public function get(string $endpoint, array $params = []): array
    {
        $sessionToken = $this->getSessionToken();

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'App-Token' => $this->appToken,
                    'Session-Token' => $sessionToken,
                ])
                ->get("{$this->apiUrl}/{$endpoint}", $params);

            if (!$response->successful()) {
                // Si falla por sesión expirada, reintentar con nueva sesión
                if ($response->status() === 401) {
                    Cache::forget('glpi_session_token');
                    $this->sessionToken = null;
                    return $this->get($endpoint, $params); // Retry
                }

                throw new GlpiException(
                    "Error en petición GET a GLPI: {$response->status()}"
                );
            }

            return $response->json();

        } catch (GlpiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new GlpiConnectionException(
                "Error de conexión con GLPI: {$e->getMessage()}"
            );
        }
    }

    /**
     * Realiza una petición POST a la API de GLPI
     */
    public function post(string $endpoint, array $data = []): array
    {
        $sessionToken = $this->getSessionToken();

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'App-Token' => $this->appToken,
                    'Session-Token' => $sessionToken,
                ])
                ->post("{$this->apiUrl}/{$endpoint}", $data);

            if (!$response->successful()) {
                if ($response->status() === 401) {
                    Cache::forget('glpi_session_token');
                    $this->sessionToken = null;
                    return $this->post($endpoint, $data); // Retry
                }

                throw new GlpiException(
                    "Error en petición POST a GLPI: {$response->status()}"
                );
            }

            return $response->json();

        } catch (GlpiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new GlpiConnectionException(
                "Error de conexión con GLPI: {$e->getMessage()}"
            );
        }
    }

    /**
     * Realiza una petición PUT a la API de GLPI
     */
    public function put(string $endpoint, array $data = []): array
    {
        $sessionToken = $this->getSessionToken();

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'App-Token' => $this->appToken,
                    'Session-Token' => $sessionToken,
                ])
                ->put("{$this->apiUrl}/{$endpoint}", $data);

            if (!$response->successful()) {
                if ($response->status() === 401) {
                    Cache::forget('glpi_session_token');
                    $this->sessionToken = null;
                    return $this->put($endpoint, $data); // Retry
                }

                throw new GlpiException(
                    "Error en petición PUT a GLPI: {$response->status()}"
                );
            }

            return $response->json();

        } catch (GlpiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new GlpiConnectionException(
                "Error de conexión con GLPI: {$e->getMessage()}"
            );
        }
    }

    /**
     * Realiza una petición DELETE a la API de GLPI
     */
    public function delete(string $endpoint, array $params = []): bool
    {
        $sessionToken = $this->getSessionToken();

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'App-Token' => $this->appToken,
                    'Session-Token' => $sessionToken,
                ])
                ->delete("{$this->apiUrl}/{$endpoint}", $params);

            if (!$response->successful()) {
                if ($response->status() === 401) {
                    Cache::forget('glpi_session_token');
                    $this->sessionToken = null;
                    return $this->delete($endpoint, $params); // Retry
                }

                throw new GlpiException(
                    "Error en petición DELETE a GLPI: {$response->status()}"
                );
            }

            return true;

        } catch (GlpiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new GlpiConnectionException(
                "Error de conexión con GLPI: {$e->getMessage()}"
            );
        }
    }

    /**
     * Obtiene un item específico de GLPI
     */
    public function getItem(string $itemType, int $id, array $params = []): array
    {
        return $this->get("{$itemType}/{$id}", $params);
    }

    /**
     * Obtiene múltiples items de GLPI
     */
    public function getItems(string $itemType, array $params = []): array
    {
        return $this->get($itemType, $params);
    }

    /**
     * Crea un nuevo item en GLPI
     */
    public function createItem(string $itemType, array $data): array
    {
        return $this->post($itemType, ['input' => $data]);
    }

    /**
     * Actualiza un item en GLPI
     */
    public function updateItem(string $itemType, int $id, array $data): array
    {
        return $this->put("{$itemType}/{$id}", ['input' => $data]);
    }

    /**
     * Elimina un item de GLPI
     */
    public function deleteItem(string $itemType, int $id, bool $force = false): bool
    {
        $params = $force ? ['force_purge' => true] : [];
        return $this->delete("{$itemType}/{$id}", $params);
    }

    /**
     * Busca items en GLPI
     */
    public function searchItems(string $itemType, array $criteria = []): array
    {
        return $this->get("search/{$itemType}", ['criteria' => $criteria]);
    }

    /**
     * Obtiene el perfil del usuario actual
     */
    public function getMyProfiles(): array
    {
        return $this->get('getMyProfiles');
    }

    /**
     * Obtiene las entidades activas del usuario actual
     */
    public function getActiveEntities(): array
    {
        return $this->get('getActiveEntities');
    }

    /**
     * Obtiene información completa de la sesión
     */
    public function getFullSession(): array
    {
        return $this->get('getFullSession');
    }
}
