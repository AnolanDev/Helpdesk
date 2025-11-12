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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Información básica
            $table->string('ticket_number')->unique(); // TKT-2025-0001
            $table->string('title');
            $table->text('description');

            // Usuario que reporta el ticket
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('user_name')->nullable(); // Cache del nombre
            $table->string('user_email')->nullable(); // Cache del email

            // Asignación
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('assigned_name')->nullable(); // Cache del nombre del asignado
            $table->timestamp('assigned_at')->nullable();

            // Estado y prioridad
            $table->enum('status', [
                'nuevo',
                'abierto',
                'en_progreso',
                'pendiente',
                'resuelto',
                'cerrado',
                'cancelado'
            ])->default('nuevo');

            $table->enum('priority', [
                'baja',
                'normal',
                'alta',
                'urgente'
            ])->default('normal');

            // Categoría
            $table->enum('category', [
                'hardware',
                'software',
                'red',
                'acceso',
                'correo',
                'impresora',
                'telefonia',
                'otro'
            ])->nullable();

            // Ubicación y entidad (para empresas multi-sede)
            $table->string('location')->nullable();
            $table->string('department')->nullable();

            // Integración con GLPI (opcional)
            $table->unsignedBigInteger('glpi_ticket_id')->nullable()->unique();
            $table->timestamp('synced_with_glpi_at')->nullable();

            // Tiempos de resolución
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('resolution_time')->nullable(); // En minutos

            // SLA (Service Level Agreement)
            $table->timestamp('due_date')->nullable();
            $table->boolean('is_overdue')->default(false);

            // Valoración
            $table->integer('satisfaction_rating')->nullable(); // 1-5
            $table->text('satisfaction_comment')->nullable();

            // Metadata
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices para búsqueda rápida
            $table->index('ticket_number');
            $table->index('user_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('priority');
            $table->index('category');
            $table->index('glpi_ticket_id');
            $table->index('created_at');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
