<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de SLA (Service Level Agreement)
    |--------------------------------------------------------------------------
    |
    | Define el tiempo máximo de respuesta en horas para cada nivel de prioridad.
    | Estos valores se utilizan para calcular automáticamente la fecha de
    | vencimiento (due_date) cuando se crea un ticket.
    |
    */

    'sla' => [
        'urgent' => env('TICKET_SLA_URGENT_HOURS', 4),      // 4 horas
        'high' => env('TICKET_SLA_HIGH_HOURS', 24),         // 24 horas (1 día)
        'normal' => env('TICKET_SLA_NORMAL_HOURS', 72),     // 72 horas (3 días)
        'low' => env('TICKET_SLA_LOW_HOURS', 168),          // 168 horas (7 días)
        'default' => env('TICKET_SLA_DEFAULT_HOURS', 72),   // Por defecto 3 días
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Adjuntos
    |--------------------------------------------------------------------------
    */

    'attachments' => [
        'enabled' => env('TICKET_ALLOW_ATTACHMENTS', false),
        'max_size' => env('TICKET_MAX_ATTACHMENT_SIZE', 10240), // KB (10MB por defecto)
        'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Notificaciones
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'email' => env('TICKET_NOTIFICATION_EMAIL', 'support@example.com'),
        'enabled' => env('TICKET_NOTIFICATIONS_ENABLED', true),
    ],

];
