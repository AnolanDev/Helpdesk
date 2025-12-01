<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar la columna ENUM para agregar los tipos de tareas
        DB::statement("ALTER TABLE `notifications` MODIFY `type` ENUM(
            'ticket_assigned',
            'ticket_reassigned',
            'ticket_status_changed',
            'ticket_commented',
            'ticket_resolved',
            'ticket_closed',
            'ticket_reopened',
            'task_assigned',
            'task_status_changed',
            'task_commented',
            'task_due_reminder'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los tipos originales
        DB::statement("ALTER TABLE `notifications` MODIFY `type` ENUM(
            'ticket_assigned',
            'ticket_reassigned',
            'ticket_status_changed',
            'ticket_commented',
            'ticket_resolved',
            'ticket_closed',
            'ticket_reopened'
        ) NOT NULL");
    }
};
