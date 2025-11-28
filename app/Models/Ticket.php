<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'user_id',
        'user_name',
        'user_email',
        'assigned_to',
        'assigned_name',
        'assigned_at',
        'status',
        'priority',
        'category',
        'location',
        'department',
        'empresa',
        'sucursal',
        'glpi_ticket_id',
        'synced_with_glpi_at',
        'opened_at',
        'resolved_at',
        'closed_at',
        'resolution_time',
        'due_date',
        'is_overdue',
        'satisfaction_rating',
        'satisfaction_comment',
        'custom_fields',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'opened_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'synced_with_glpi_at' => 'datetime',
        'due_date' => 'datetime',
        'is_overdue' => 'boolean',
        'satisfaction_rating' => 'integer',
        'custom_fields' => 'array',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'priority_label',
        'priority_color',
        'category_label',
    ];

    // Estados disponibles
    public const STATUS_NEW = 'nuevo';
    public const STATUS_OPEN = 'abierto';
    public const STATUS_IN_PROGRESS = 'en_progreso';
    public const STATUS_PENDING = 'pendiente';
    public const STATUS_RESOLVED = 'resuelto';
    public const STATUS_CLOSED = 'cerrado';
    public const STATUS_CANCELLED = 'cancelado';

    // Prioridades
    public const PRIORITY_LOW = 'baja';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'alta';
    public const PRIORITY_URGENT = 'urgente';

    // Categorías
    public const CATEGORY_HARDWARE = 'hardware';
    public const CATEGORY_SOFTWARE = 'software';
    public const CATEGORY_NETWORK = 'red';
    public const CATEGORY_ACCESS = 'acceso';
    public const CATEGORY_EMAIL = 'correo';
    public const CATEGORY_PRINTER = 'impresora';
    public const CATEGORY_PHONE = 'telefonia';
    public const CATEGORY_OTHER = 'otro';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar número de ticket automáticamente
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber($ticket->empresa);
            }

            // Cachear datos del usuario
            if ($ticket->user_id && !$ticket->user_name) {
                $user = User::find($ticket->user_id);
                if ($user) {
                    $ticket->user_name = $user->name;
                    $ticket->user_email = $user->email;
                }
            }

            // Establecer fecha de apertura
            if (empty($ticket->opened_at)) {
                $ticket->opened_at = now();
            }

            // Calcular fecha de vencimiento basada en SLA por prioridad
            if (empty($ticket->due_date) && $ticket->priority) {
                $ticket->due_date = static::calculateDueDate($ticket->priority);
            }
        });

        // Actualizar tiempos de resolución
        static::updating(function ($ticket) {
            // Si se marca como resuelto
            if ($ticket->isDirty('status') && $ticket->status === static::STATUS_RESOLVED && !$ticket->resolved_at) {
                $ticket->resolved_at = now();
                $ticket->resolution_time = $ticket->opened_at->diffInMinutes(now());
            }

            // Si se marca como cerrado
            if ($ticket->isDirty('status') && $ticket->status === static::STATUS_CLOSED && !$ticket->closed_at) {
                $ticket->closed_at = now();
            }

            // Si se asigna a alguien
            if ($ticket->isDirty('assigned_to') && $ticket->assigned_to) {
                $ticket->assigned_at = now();
                $assignedUser = User::find($ticket->assigned_to);
                if ($assignedUser) {
                    $ticket->assigned_name = $assignedUser->name;
                }
            }
        });
    }

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at', 'asc');
    }

    public function publicComments(): HasMany
    {
        return $this->comments()->where('is_private', false);
    }

    public function internalComments(): HasMany
    {
        return $this->comments()->where('is_private', true);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->orderBy('created_at', 'desc');
    }

    /**
     * Scopes
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            static::STATUS_NEW,
            static::STATUS_OPEN,
            static::STATUS_IN_PROGRESS,
            static::STATUS_PENDING,
        ]);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', [static::STATUS_CLOSED, static::STATUS_CANCELLED]);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', static::STATUS_RESOLVED);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true)
            ->orWhere(function ($q) {
                $q->whereNotNull('due_date')
                    ->where('due_date', '<', now())
                    ->whereNotIn('status', [static::STATUS_CLOSED, static::STATUS_CANCELLED]);
            });
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeReportedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Métodos auxiliares
     */
    public static function generateTicketNumber(string $empresa = null): string
    {
        // Obtener iniciales de la empresa
        $iniciales = static::getEmpresaIniciales($empresa);

        // Formato: ASE-20251128-0001 (incluye fecha para unicidad)
        $fecha = date('Ymd'); // YYYYMMDD

        // Obtener todos los tickets del día con el mismo prefijo
        $tickets = static::where('ticket_number', 'like', $iniciales . '-' . $fecha . '-%')
            ->pluck('ticket_number');

        $maxConsecutivo = 0;

        // Encontrar el número más alto del día
        foreach ($tickets as $ticketNumber) {
            // Formato esperado: ASE-20251128-0001
            $parts = explode('-', $ticketNumber);
            if (count($parts) === 3 && is_numeric($parts[2])) {
                $numero = (int) $parts[2];
                if ($numero > $maxConsecutivo) {
                    $maxConsecutivo = $numero;
                }
            }
        }

        $consecutivo = $maxConsecutivo + 1;

        return sprintf('%s-%s-%04d', $iniciales, $fecha, $consecutivo);
    }

    public static function getEmpresaIniciales(?string $empresa): string
    {
        return match ($empresa) {
            'Asercol' => 'ASE',
            'Sotracar' => 'SOT',
            'Ci Global Services' => 'CIG',
            'Ambientados' => 'AMB',
            default => 'TKT',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            static::STATUS_NEW,
            static::STATUS_OPEN,
            static::STATUS_IN_PROGRESS,
            static::STATUS_PENDING,
        ]);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [static::STATUS_CLOSED, static::STATUS_CANCELLED]);
    }

    public function isResolved(): bool
    {
        return $this->status === static::STATUS_RESOLVED;
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date < now() && !$this->isClosed();
    }

    public function canBeAssigned(): bool
    {
        return $this->isOpen();
    }

    public function canBeClosed(): bool
    {
        return !$this->isClosed();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            static::STATUS_NEW => 'Nuevo',
            static::STATUS_OPEN => 'Abierto',
            static::STATUS_IN_PROGRESS => 'En Progreso',
            static::STATUS_PENDING => 'Pendiente',
            static::STATUS_RESOLVED => 'Resuelto',
            static::STATUS_CLOSED => 'Cerrado',
            static::STATUS_CANCELLED => 'Cancelado',
            default => $this->status,
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            static::PRIORITY_LOW => 'Baja',
            static::PRIORITY_NORMAL => 'Normal',
            static::PRIORITY_HIGH => 'Alta',
            static::PRIORITY_URGENT => 'Urgente',
            default => $this->priority,
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            static::CATEGORY_HARDWARE => 'Hardware',
            static::CATEGORY_SOFTWARE => 'Software',
            static::CATEGORY_NETWORK => 'Red',
            static::CATEGORY_ACCESS => 'Acceso',
            static::CATEGORY_EMAIL => 'Correo',
            static::CATEGORY_PRINTER => 'Impresora',
            static::CATEGORY_PHONE => 'Telefonía',
            static::CATEGORY_OTHER => 'Otro',
            default => $this->category,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            static::STATUS_NEW => 'blue',
            static::STATUS_OPEN => 'cyan',
            static::STATUS_IN_PROGRESS => 'yellow',
            static::STATUS_PENDING => 'orange',
            static::STATUS_RESOLVED => 'green',
            static::STATUS_CLOSED => 'gray',
            static::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            static::PRIORITY_LOW => 'gray',
            static::PRIORITY_NORMAL => 'blue',
            static::PRIORITY_HIGH => 'orange',
            static::PRIORITY_URGENT => 'red',
            default => 'gray',
        };
    }

    /**
     * Acciones sobre el ticket
     */
    public function assignTo(User $user): void
    {
        $this->update([
            'assigned_to' => $user->id,
            'assigned_name' => $user->name,
            'assigned_at' => now(),
            'status' => $this->status === static::STATUS_NEW ? static::STATUS_OPEN : $this->status,
        ]);
    }

    public function markAsResolved(string $solution = null): void
    {
        $this->update([
            'status' => static::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolution_time' => $this->opened_at->diffInMinutes(now()),
        ]);

        if ($solution) {
            $this->addComment($solution, 'solution');
        }
    }

    public function markAsClosed(): void
    {
        $this->update([
            'status' => static::STATUS_CLOSED,
            'closed_at' => now(),
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => static::STATUS_OPEN,
            'resolved_at' => null,
            'closed_at' => null,
            'resolution_time' => null,
        ]);
    }

    public function addComment(string $comment, string $type = 'public', bool $isPrivate = false): TicketComment
    {
        return $this->comments()->create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'comment' => $comment,
            'type' => $type,
            'is_private' => $isPrivate,
        ]);
    }

    /**
     * Métodos estáticos para obtener opciones
     */
    public static function getStatuses(): array
    {
        return [
            static::STATUS_NEW => 'Nuevo',
            static::STATUS_OPEN => 'Abierto',
            static::STATUS_IN_PROGRESS => 'En Progreso',
            static::STATUS_PENDING => 'Pendiente',
            static::STATUS_RESOLVED => 'Resuelto',
            static::STATUS_CLOSED => 'Cerrado',
            static::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            static::PRIORITY_LOW => 'Baja',
            static::PRIORITY_NORMAL => 'Normal',
            static::PRIORITY_HIGH => 'Alta',
            static::PRIORITY_URGENT => 'Urgente',
        ];
    }

    public static function getCategories(): array
    {
        return [
            static::CATEGORY_HARDWARE => 'Hardware',
            static::CATEGORY_SOFTWARE => 'Software',
            static::CATEGORY_NETWORK => 'Red',
            static::CATEGORY_ACCESS => 'Acceso',
            static::CATEGORY_EMAIL => 'Correo',
            static::CATEGORY_PRINTER => 'Impresora',
            static::CATEGORY_PHONE => 'Telefonía',
            static::CATEGORY_OTHER => 'Otro',
        ];
    }

    public static function getEmpresas(): array
    {
        return [
            'Asercol',
            'Sotracar',
            'Ci Global Services',
            'Ambientados',
        ];
    }

    public static function getSucursalesByEmpresa(): array
    {
        return [
            'Asercol' => [
                'Santa Marta',
                'Cucuta',
                'Barranquilla',
                'Cartagena',
                'Bogota',
                'Medellin',
                'Buenaventura',
                'Ipiales',
            ],
            'Ci Global Services' => [
                'Cartagena',
                'Bogota',
            ],
            'Sotracar' => [
                'Cartagena',
                'Bogota',
                'Buenaventura',
            ],
            'Ambientados' => [
                'Cartagena',
            ],
        ];
    }

    /**
     * Calcular fecha de vencimiento basada en SLA por prioridad
     */
    public static function calculateDueDate(string $priority): ?\Carbon\Carbon
    {
        $slaHours = match ($priority) {
            static::PRIORITY_URGENT => config('tickets.sla.urgent', 4),
            static::PRIORITY_HIGH => config('tickets.sla.high', 24),
            static::PRIORITY_NORMAL => config('tickets.sla.normal', 72),
            static::PRIORITY_LOW => config('tickets.sla.low', 168),
            default => config('tickets.sla.default', 72),
        };

        return now()->addHours((int) $slaHours);
    }
}
