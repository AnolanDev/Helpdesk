<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Usuario que recibe la notificación
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Tipo de notificación
            $table->enum('type', [
                'ticket_assigned',      // Ticket asignado
                'ticket_reassigned',    // Ticket reasignado
                'ticket_status_changed', // Estado cambiado
                'ticket_commented',     // Nuevo comentario
                'ticket_resolved',      // Ticket resuelto
                'ticket_closed',        // Ticket cerrado
                'ticket_reopened',      // Ticket reabierto
            ]);

            // Título y mensaje
            $table->string('title');
            $table->text('message');

            // Relacionado con ticket (opcional)
            $table->foreignId('ticket_id')->nullable()->constrained()->onDelete('cascade');

            // Usuario que generó la acción (opcional)
            $table->foreignId('action_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action_by_name')->nullable(); // Cache del nombre

            // URL para redirigir al hacer clic
            $table->string('url')->nullable();

            // Estado
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();

            // Metadata adicional
            $table->json('data')->nullable();

            $table->timestamps();

            // Índices para optimización
            $table->index(['user_id', 'read']);
            $table->index('ticket_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
