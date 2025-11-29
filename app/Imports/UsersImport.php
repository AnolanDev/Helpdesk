<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UserImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UsersImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    use SkipsFailures;

    protected $userImport;
    protected $errors = [];
    protected $successfulRows = 0;
    protected $failedRows = 0;

    public function __construct(UserImport $userImport)
    {
        $this->userImport = $userImport;
    }

    /**
     * Procesar la colección de datos
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 porque Excel empieza en 1 y tiene header

            try {
                // Validar la fila
                $validator = Validator::make($row->toArray(), $this->rules(), $this->customValidationMessages());

                if ($validator->fails()) {
                    $this->failedRows++;
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'errors' => $validator->errors()->all(),
                        'data' => $row->toArray(),
                    ];
                    continue;
                }

                // Preparar datos
                $userData = [
                    'name' => $row['nombre'] ?? $row['name'],
                    'email' => strtolower(trim($row['email'] ?? $row['correo'])),
                    'password' => Hash::make($row['password'] ?? $row['contrasena'] ?? 'password123'),
                    'tipo_usuario' => $this->mapUserType($row['tipo'] ?? $row['tipo_usuario'] ?? 'usuario_final'),
                    'empresa' => $row['empresa'] ?? null,
                    'sucursal' => $row['sucursal'] ?? null,
                    'phone' => $row['telefono'] ?? $row['phone'] ?? null,
                    'is_active' => $this->parseBoolean($row['activo'] ?? $row['is_active'] ?? 'si'),
                    'email_verified_at' => now(),
                ];

                // Crear o actualizar usuario
                $user = User::updateOrCreate(
                    ['email' => $userData['email']],
                    $userData
                );

                $this->successfulRows++;
            } catch (\Exception $e) {
                $this->failedRows++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => [$e->getMessage()],
                    'data' => $row->toArray(),
                ];
            }
        }

        // Actualizar estadísticas del import
        $this->userImport->update([
            'total_rows' => $this->successfulRows + $this->failedRows,
            'successful_rows' => $this->successfulRows,
            'failed_rows' => $this->failedRows,
            'errors' => $this->errors,
        ]);
    }

    /**
     * Reglas de validación
     */
    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'tipo' => 'nullable|string',
            'empresa' => 'nullable|string|max:255',
            'sucursal' => 'nullable|string|max:255',
        ];
    }

    /**
     * Mensajes de validación personalizados
     */
    protected function customValidationMessages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser una dirección válida',
            'email.unique' => 'El email ya existe en el sistema',
        ];
    }

    /**
     * Mapear tipo de usuario
     */
    protected function mapUserType(?string $type): string
    {
        if (!$type) {
            return 'usuario_final';
        }

        $type = strtolower(trim($type));

        $mapping = [
            'admin' => 'admin',
            'administrador' => 'admin',
            'tech' => 'tech',
            'tecnico' => 'tech',
            'técnico' => 'tech',
            'soporte' => 'tech',
            'user' => 'usuario_final',
            'usuario' => 'usuario_final',
            'usuario_final' => 'usuario_final',
            'final' => 'usuario_final',
        ];

        return $mapping[$type] ?? 'usuario_final';
    }

    /**
     * Parsear valores booleanos
     */
    protected function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['si', 'sí', 'yes', 'y', '1', 'true', 'activo'], true);
    }

    /**
     * Tamaño del batch
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Tamaño del chunk
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Obtener errores
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener resumen
     */
    public function getSummary(): array
    {
        return [
            'total' => $this->successfulRows + $this->failedRows,
            'successful' => $this->successfulRows,
            'failed' => $this->failedRows,
            'errors' => $this->errors,
        ];
    }
}
