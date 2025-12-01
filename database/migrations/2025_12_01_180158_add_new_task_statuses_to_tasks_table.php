<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega soporte para los nuevos estados del sistema de tareas:
     * - scheduled: Tarea programada para ejecutarse en el futuro
     * - blocked: Tarea bloqueada por dependencias u obstáculos
     * - archived: Tarea archivada (completada o cancelada hace tiempo)
     *
     * Estados completos del sistema:
     * 1. todo (Pendiente)
     * 2. scheduled (Programada)
     * 3. in_progress (En Progreso)
     * 4. blocked (Bloqueada)
     * 5. review (En Revisión)
     * 6. done (Completada)
     * 7. cancelled (Cancelada)
     * 8. archived (Archivada)
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Agregar campos para rastrear cambios de estado
            $table->string('previous_status')->nullable()->after('status');
            $table->timestamp('status_changed_at')->nullable()->after('previous_status');
            $table->foreignId('status_changed_by')->nullable()->constrained('users')->nullOnDelete()->after('status_changed_at');

            // Agregar campo para la razón del bloqueo
            $table->text('blocked_reason')->nullable()->after('status_changed_by');

            // Agregar índice para búsquedas por estado previo
            $table->index('previous_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['status_changed_by']);
            $table->dropIndex(['previous_status']);
            $table->dropColumn([
                'previous_status',
                'status_changed_at',
                'status_changed_by',
                'blocked_reason',
            ]);
        });
    }
};
