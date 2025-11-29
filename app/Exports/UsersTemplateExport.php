<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class UsersTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * Array de datos de ejemplo
     */
    public function array(): array
    {
        return [
            [
                'Juan Pérez',
                'juan.perez@ejemplo.com',
                'password123',
                'usuario_final',
                'Asercol',
                'Cartagena',
                '+57 300 1234567',
                'si',
            ],
            [
                'María González',
                'maria.gonzalez@ejemplo.com',
                'password123',
                'tech',
                'Sotracar',
                'Bogota',
                '+57 310 9876543',
                'si',
            ],
            [
                'Carlos Admin',
                'carlos.admin@ejemplo.com',
                'password123',
                'admin',
                'Ci Global Services',
                'Cartagena',
                '+57 320 5551234',
                'si',
            ],
        ];
    }

    /**
     * Encabezados
     */
    public function headings(): array
    {
        return [
            'Nombre',
            'Email',
            'Contraseña',
            'Tipo (usuario_final/tech/admin)',
            'Empresa',
            'Sucursal',
            'Teléfono',
            'Activo (si/no)',
        ];
    }

    /**
     * Estilos
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'], // primary-600
                ],
            ],
        ];
    }

    /**
     * Ancho de columnas
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25, // Nombre
            'B' => 30, // Email
            'C' => 15, // Contraseña
            'D' => 30, // Tipo
            'E' => 25, // Empresa
            'F' => 20, // Sucursal
            'G' => 18, // Teléfono
            'H' => 15, // Activo
        ];
    }
}
