<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Iniciando generación de datos de prueba...');

        // 1. Crear usuarios de prueba
        $this->command->info('👥 Creando usuarios de prueba...');
        $users = $this->createUsers();

        // 2. Verificar o crear settings
        $this->command->info('⚙️ Verificando configuraciones de SLA...');
        $this->ensureSettings();

        // 3. Crear tickets de prueba
        $this->command->info('🎫 Creando tickets de prueba...');
        $this->createTickets($users);

        $this->command->info('✅ Datos de prueba generados exitosamente!');
        $this->command->newLine();
        $this->displayCredentials($users);
    }

    /**
     * Crear usuarios de prueba
     */
    protected function createUsers(): array
    {
        $password = Hash::make('password');

        $users = [
            'admin' => User::updateOrCreate(
                ['email' => 'admin@helptech.com'],
                [
                    'name' => 'Administrador Principal',
                    'password' => $password,
                    'tipo_usuario' => 'admin',
                    'empresa' => 'Asercol',
                    'sucursal' => 'Cartagena',
                    'departamento' => 'TI',
                    'cargo' => 'Administrador de Sistemas',
                    'telefono' => '+57 300 1111111',
                    'activo' => true,
                    'email_verified_at' => now(),
                ]
            ),

            'tech1' => User::updateOrCreate(
                ['email' => 'tech1@helptech.com'],
                [
                    'name' => 'Juan Técnico Gómez',
                    'password' => $password,
                    'tipo_usuario' => 'tech',
                    'empresa' => 'Asercol',
                    'sucursal' => 'Cartagena',
                    'departamento' => 'Soporte Técnico',
                    'cargo' => 'Técnico de Soporte',
                    'telefono' => '+57 300 2222222',
                    'activo' => true,
                    'email_verified_at' => now(),
                ]
            ),

            'tech2' => User::updateOrCreate(
                ['email' => 'tech2@helptech.com'],
                [
                    'name' => 'María Soporte Rodríguez',
                    'password' => $password,
                    'tipo_usuario' => 'tech',
                    'empresa' => 'Sotracar',
                    'sucursal' => 'Bogota',
                    'departamento' => 'Soporte Técnico',
                    'cargo' => 'Técnico de Soporte',
                    'telefono' => '+57 300 3333333',
                    'activo' => true,
                    'email_verified_at' => now(),
                ]
            ),

            'user1' => User::updateOrCreate(
                ['email' => 'usuario1@helptech.com'],
                [
                    'name' => 'Pedro Usuario Pérez',
                    'password' => $password,
                    'tipo_usuario' => 'user',
                    'empresa' => 'Asercol',
                    'sucursal' => 'Cartagena',
                    'departamento' => 'Ventas',
                    'cargo' => 'Vendedor',
                    'telefono' => '+57 300 4444444',
                    'activo' => true,
                    'email_verified_at' => now(),
                ]
            ),

            'user2' => User::updateOrCreate(
                ['email' => 'usuario2@helptech.com'],
                [
                    'name' => 'Ana Usuario López',
                    'password' => $password,
                    'tipo_usuario' => 'user',
                    'empresa' => 'Sotracar',
                    'sucursal' => 'Bogota',
                    'departamento' => 'Contabilidad',
                    'cargo' => 'Contador',
                    'telefono' => '+57 300 5555555',
                    'activo' => true,
                    'email_verified_at' => now(),
                ]
            ),

            'user3' => User::updateOrCreate(
                ['email' => 'usuario3@helptech.com'],
                [
                    'name' => 'Carlos Usuario Ramírez',
                    'password' => $password,
                    'tipo_usuario' => 'user',
                    'empresa' => 'Ci Global Services',
                    'sucursal' => 'Cartagena',
                    'departamento' => 'Logística',
                    'cargo' => 'Coordinador',
                    'telefono' => '+57 300 6666666',
                    'activo' => true,
                    'email_verified_at' => now(),
                ]
            ),
        ];

        $this->command->info('   ✓ ' . count($users) . ' usuarios creados');
        return $users;
    }

    /**
     * Asegurar que existan las configuraciones de SLA
     */
    protected function ensureSettings(): void
    {
        $settings = [
            [
                'key' => 'sla_urgent_hours',
                'value' => '4',
                'type' => 'integer',
                'group' => 'sla',
                'label' => 'SLA - Urgente (horas)',
                'description' => 'Tiempo máximo de resolución para tickets urgentes',
            ],
            [
                'key' => 'sla_high_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'sla',
                'label' => 'SLA - Alta (horas)',
                'description' => 'Tiempo máximo de resolución para tickets de prioridad alta',
            ],
            [
                'key' => 'sla_normal_hours',
                'value' => '72',
                'type' => 'integer',
                'group' => 'sla',
                'label' => 'SLA - Normal (horas)',
                'description' => 'Tiempo máximo de resolución para tickets de prioridad normal',
            ],
            [
                'key' => 'sla_low_hours',
                'value' => '168',
                'type' => 'integer',
                'group' => 'sla',
                'label' => 'SLA - Baja (horas)',
                'description' => 'Tiempo máximo de resolución para tickets de prioridad baja',
            ],
            [
                'key' => 'sla_warning_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'sla',
                'label' => 'Advertencia de vencimiento (horas)',
                'description' => 'Mostrar advertencia cuando falten X horas para el vencimiento',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('   ✓ Configuraciones de SLA verificadas');
    }

    /**
     * Crear tickets de prueba
     */
    protected function createTickets(array $users): void
    {
        $ticketsData = [
            // Ticket 1: Urgente - Abierto
            [
                'title' => 'Servidor de producción caído',
                'description' => 'El servidor principal de producción no responde. Los clientes no pueden acceder al sistema. Necesitamos solución inmediata.',
                'priority' => 'urgente',
                'category' => 'red',
                'status' => 'en_progreso',
                'user' => $users['user1'],
                'assigned_to' => $users['tech1'],
            ],

            // Ticket 2: Alta - Nuevo
            [
                'title' => 'Error al procesar pagos en línea',
                'description' => 'Los usuarios reportan errores al intentar realizar pagos. El mensaje dice "Error de conexión con pasarela".',
                'priority' => 'alta',
                'category' => 'software',
                'status' => 'nuevo',
                'user' => $users['user2'],
            ],

            // Ticket 3: Normal - Asignado
            [
                'title' => 'Solicitud de acceso a carpeta compartida',
                'description' => 'Necesito acceso a la carpeta compartida "Ventas 2025" en el servidor. Mi usuario es pedro.perez',
                'priority' => 'normal',
                'category' => 'acceso',
                'status' => 'abierto',
                'user' => $users['user1'],
                'assigned_to' => $users['tech2'],
            ],

            // Ticket 4: Baja - Pendiente
            [
                'title' => 'Actualización de software de Office',
                'description' => 'Mi versión de Office está desactualizada. ¿Pueden actualizar a la última versión?',
                'priority' => 'baja',
                'category' => 'software',
                'status' => 'pendiente',
                'user' => $users['user3'],
                'assigned_to' => $users['tech1'],
            ],

            // Ticket 5: Normal - Resuelto
            [
                'title' => 'Impresora no imprime',
                'description' => 'La impresora de la oficina 301 no está imprimiendo. Aparece en línea pero no saca los documentos.',
                'priority' => 'normal',
                'category' => 'impresora',
                'status' => 'resuelto',
                'user' => $users['user2'],
                'assigned_to' => $users['tech2'],
                'resolved' => true,
            ],

            // Ticket 6: Alta - Cerrado
            [
                'title' => 'Correo corporativo no sincroniza',
                'description' => 'No puedo recibir correos en mi Outlook. Los correos llegan al webmail pero no al programa.',
                'priority' => 'alta',
                'category' => 'correo',
                'status' => 'cerrado',
                'user' => $users['user1'],
                'assigned_to' => $users['tech1'],
                'resolved' => true,
                'closed' => true,
            ],

            // Ticket 7: Normal - Nuevo (sin asignar)
            [
                'title' => 'Solicitud de instalación de software',
                'description' => 'Necesito que instalen Adobe Acrobat Pro en mi computador para poder editar PDFs.',
                'priority' => 'normal',
                'category' => 'software',
                'status' => 'nuevo',
                'user' => $users['user3'],
            ],

            // Ticket 8: Urgente - Vencido
            [
                'title' => 'No tengo acceso a la red',
                'description' => 'Mi computador no se conecta a la red de la empresa. Probé reiniciar pero sigue igual. No puedo trabajar.',
                'priority' => 'urgente',
                'category' => 'red',
                'status' => 'abierto',
                'user' => $users['user2'],
                'assigned_to' => $users['tech2'],
                'overdue' => true,
            ],
        ];

        foreach ($ticketsData as $index => $ticketData) {
            $ticket = Ticket::create([
                'title' => $ticketData['title'],
                'description' => $ticketData['description'],
                'user_id' => $ticketData['user']->id,
                'user_name' => $ticketData['user']->name,
                'user_email' => $ticketData['user']->email,
                'assigned_to' => $ticketData['assigned_to']->id ?? null,
                'assigned_name' => $ticketData['assigned_to']->name ?? null,
                'status' => $ticketData['status'],
                'priority' => $ticketData['priority'],
                'category' => $ticketData['category'],
                'empresa' => $ticketData['user']->empresa,
                'sucursal' => $ticketData['user']->sucursal,
                'opened_at' => now()->subDays(rand(1, 5)),
            ]);

            // Si está vencido, ajustar la fecha de vencimiento
            if (isset($ticketData['overdue']) && $ticketData['overdue']) {
                $ticket->update([
                    'due_date' => now()->subHours(rand(1, 48)),
                    'is_overdue' => true,
                ]);
            }

            // Si está resuelto, agregar datos de resolución
            if (isset($ticketData['resolved']) && $ticketData['resolved']) {
                $ticket->update([
                    'resolved_at' => now()->subHours(rand(1, 24)),
                    'resolution_time' => rand(30, 1440), // 30 min a 24 horas
                ]);

                // Agregar comentario de solución
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticketData['assigned_to']->id,
                    'user_name' => $ticketData['assigned_to']->name,
                    'comment' => 'Problema resuelto: ' . $this->getSolutionText($ticketData['category']),
                    'type' => 'solution',
                    'is_private' => false,
                ]);

                // Actividad de resolución
                TicketActivity::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticketData['assigned_to']->id,
                    'user_name' => $ticketData['assigned_to']->name,
                    'action' => 'status_changed',
                    'description' => 'Estado cambiado a: Resuelto',
                    'old_value' => 'en_progreso',
                    'new_value' => 'resuelto',
                ]);
            }

            // Si está cerrado
            if (isset($ticketData['closed']) && $ticketData['closed']) {
                $ticket->update([
                    'closed_at' => now()->subHours(rand(1, 12)),
                ]);
            }

            // Agregar algunos comentarios a tickets específicos
            if (in_array($index, [0, 2, 4, 7])) {
                $this->addCommentsToTicket($ticket, $ticketData['user'], $ticketData['assigned_to'] ?? null);
            }

            // Agregar actividades
            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => $ticketData['user']->id,
                'user_name' => $ticketData['user']->name,
                'action' => 'created',
                'description' => 'Ticket creado',
            ]);

            if (isset($ticketData['assigned_to'])) {
                TicketActivity::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $users['admin']->id,
                    'user_name' => $users['admin']->name,
                    'action' => 'assigned',
                    'description' => 'Ticket asignado a: ' . $ticketData['assigned_to']->name,
                    'new_value' => $ticketData['assigned_to']->id,
                ]);
            }
        }

        $this->command->info('   ✓ ' . count($ticketsData) . ' tickets creados con actividades y comentarios');
    }

    /**
     * Agregar comentarios a un ticket
     */
    protected function addCommentsToTicket(Ticket $ticket, User $user, ?User $tech): void
    {
        // Comentario del usuario
        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'comment' => 'Esto es urgente, necesito ayuda lo antes posible.',
            'type' => 'public',
            'is_private' => false,
            'created_at' => now()->subHours(rand(2, 48)),
        ]);

        if ($tech) {
            // Comentario del técnico
            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $tech->id,
                'user_name' => $tech->name,
                'comment' => 'Entendido, estoy revisando el caso. Te mantendré informado.',
                'type' => 'public',
                'is_private' => false,
                'created_at' => now()->subHours(rand(1, 24)),
            ]);

            // Nota interna del técnico
            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $tech->id,
                'user_name' => $tech->name,
                'comment' => 'Nota interna: Revisar configuración del servidor antes de continuar.',
                'type' => 'internal',
                'is_private' => true,
                'created_at' => now()->subHours(rand(1, 20)),
            ]);
        }
    }

    /**
     * Obtener texto de solución según categoría
     */
    protected function getSolutionText(string $category): string
    {
        $solutions = [
            'hardware' => 'Se reemplazó el componente defectuoso y se verificó el correcto funcionamiento.',
            'software' => 'Se reinstaló la aplicación y se configuraron los permisos necesarios.',
            'red' => 'Se restableció la conexión de red y se verificó la conectividad.',
            'acceso' => 'Se otorgaron los permisos de acceso solicitados.',
            'correo' => 'Se reconfiguró la cuenta de correo y se sincronizaron los mensajes.',
            'impresora' => 'Se reinició el servicio de impresión y se actualizaron los drivers.',
            'telefonia' => 'Se verificó la configuración telefónica y se restableció el servicio.',
            'otro' => 'Se atendió la solicitud según lo requerido.',
        ];

        return $solutions[$category] ?? 'Problema resuelto satisfactoriamente.';
    }

    /**
     * Mostrar credenciales de acceso
     */
    protected function displayCredentials(array $users): void
    {
        $this->command->newLine();
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📋 CREDENCIALES DE ACCESO');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();

        $credentials = [
            ['Tipo', 'Email', 'Contraseña', 'Nombre'],
            ['────────────', '─────────────────────────', '──────────', '──────────────────────────'],
            ['Admin', 'admin@helptech.com', 'password', 'Administrador Principal'],
            ['Técnico', 'tech1@helptech.com', 'password', 'Juan Técnico Gómez'],
            ['Técnico', 'tech2@helptech.com', 'password', 'María Soporte Rodríguez'],
            ['Usuario', 'usuario1@helptech.com', 'password', 'Pedro Usuario Pérez'],
            ['Usuario', 'usuario2@helptech.com', 'password', 'Ana Usuario López'],
            ['Usuario', 'usuario3@helptech.com', 'password', 'Carlos Usuario Ramírez'],
        ];

        foreach ($credentials as $row) {
            $this->command->line(sprintf(
                '  %-12s  %-25s  %-10s  %-30s',
                $row[0],
                $row[1],
                $row[2],
                $row[3]
            ));
        }

        $this->command->newLine();
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();
        $this->command->info('🎯 URLs de acceso:');
        $this->command->line('  • Dashboard: http://localhost:8000/dashboard');
        $this->command->line('  • Tickets: http://localhost:8000/tickets');
        $this->command->line('  • Usuarios: http://localhost:8000/users');
        $this->command->line('  • Importar: http://localhost:8000/users-import');
        $this->command->line('  • Configuración: http://localhost:8000/settings');
        $this->command->newLine();
    }
}
