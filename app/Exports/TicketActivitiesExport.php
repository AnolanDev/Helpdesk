<?php

namespace App\Exports;

use App\Models\TicketActivity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketActivitiesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $ticketId;

    public function __construct($ticketId)
    {
        $this->ticketId = $ticketId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return TicketActivity::where('ticket_id', $this->ticketId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Map data for each row
     */
    public function map($activity): array
    {
        return [
            $activity->created_at->format('d/m/Y H:i:s'),
            $activity->user_name ?? 'Sistema',
            $this->getActivityTypeName($activity->activity_type),
            $activity->description,
            $activity->old_value ?? '-',
            $activity->new_value ?? '-',
        ];
    }

    /**
     * Column headings
     */
    public function headings(): array
    {
        return [
            'Fecha y Hora',
            'Usuario',
            'Tipo de Actividad',
            'Descripción',
            'Valor Anterior',
            'Valor Nuevo',
        ];
    }

    /**
     * Styles for the spreadsheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as header
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    /**
     * Get human-readable activity type name
     */
    private function getActivityTypeName(string $type): string
    {
        return match ($type) {
            'created' => 'Creado',
            'assigned' => 'Asignado',
            'reassigned' => 'Reasignado',
            'status_changed' => 'Cambio de Estado',
            'priority_changed' => 'Cambio de Prioridad',
            'category_changed' => 'Cambio de Categoría',
            'commented' => 'Comentario',
            'resolved' => 'Resuelto',
            'closed' => 'Cerrado',
            'reopened' => 'Reabierto',
            'updated' => 'Actualizado',
            default => ucfirst($type),
        };
    }
}
