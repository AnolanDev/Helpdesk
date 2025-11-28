<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora de Actividades - Ticket {{ $ticket->ticket_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }

        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20pt;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 10pt;
            opacity: 0.9;
        }

        .ticket-info {
            background-color: #f3f4f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .ticket-info table {
            width: 100%;
        }

        .ticket-info td {
            padding: 5px 10px;
        }

        .ticket-info .label {
            font-weight: bold;
            width: 30%;
            color: #666;
        }

        .timeline {
            position: relative;
        }

        .activity {
            position: relative;
            padding-left: 40px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .activity::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: -20px;
            width: 2px;
            background-color: #e5e7eb;
        }

        .activity:last-child::before {
            bottom: 0;
        }

        .activity-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #4F46E5;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #4F46E5;
        }

        .activity-content {
            background-color: #fff;
            border: 1px solid #e5e7eb;
            padding: 10px;
            border-radius: 5px;
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .activity-type {
            color: #4F46E5;
            font-size: 9pt;
        }

        .activity-date {
            color: #999;
            font-size: 8pt;
        }

        .activity-description {
            margin-bottom: 5px;
            color: #555;
        }

        .activity-details {
            font-size: 9pt;
            color: #666;
            background-color: #f9fafb;
            padding: 5px;
            border-left: 3px solid #4F46E5;
            margin-top: 5px;
        }

        .value-change {
            margin-top: 5px;
        }

        .old-value {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9pt;
        }

        .new-value {
            background-color: #d1fae5;
            color: #065f46;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9pt;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Bitácora de Actividades</h1>
        <p>Ticket {{ $ticket->ticket_number }} - {{ $ticket->title }}</p>
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Ticket Info -->
    <div class="ticket-info">
        <table>
            <tr>
                <td class="label">Ticket:</td>
                <td>{{ $ticket->ticket_number }}</td>
                <td class="label">Estado:</td>
                <td>{{ $ticket->status_label }}</td>
            </tr>
            <tr>
                <td class="label">Reportado por:</td>
                <td>{{ $ticket->user_name }}</td>
                <td class="label">Prioridad:</td>
                <td>{{ $ticket->priority_label }}</td>
            </tr>
            <tr>
                <td class="label">Asignado a:</td>
                <td>{{ $ticket->assigned_name ?? 'Sin asignar' }}</td>
                <td class="label">Categoría:</td>
                <td>{{ $ticket->category_label }}</td>
            </tr>
            <tr>
                <td class="label">Creado:</td>
                <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                <td class="label">Empresa:</td>
                <td>{{ $ticket->empresa ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- Activities Timeline -->
    <div class="timeline">
        @forelse($activities as $activity)
            <div class="activity">
                <div class="activity-icon"></div>
                <div class="activity-content">
                    <div class="activity-header">
                        <span class="activity-type">
                            {{ match($activity->activity_type) {
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
                                default => ucfirst($activity->activity_type),
                            } }}
                        </span>
                        <span class="activity-date">
                            {{ $activity->created_at->format('d/m/Y H:i:s') }}
                        </span>
                    </div>
                    <div class="activity-description">
                        {{ $activity->description }}
                    </div>
                    <div class="activity-details">
                        Usuario: {{ $activity->user_name ?? 'Sistema' }}
                    </div>
                    @if($activity->old_value || $activity->new_value)
                        <div class="value-change">
                            @if($activity->old_value)
                                <span class="old-value">De: {{ $activity->old_value }}</span>
                            @endif
                            @if($activity->new_value)
                                <span class="new-value">A: {{ $activity->new_value }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p style="text-align: center; color: #999; padding: 20px;">No hay actividades registradas.</p>
        @endforelse
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Este documento fue generado automáticamente por el sistema de HelpTech.</p>
        <p>Total de actividades: {{ $activities->count() }}</p>
    </div>
</body>
</html>
