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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Identificación de la tarea
            $table->string('task_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();

            // Usuario que crea la tarea
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name')->nullable();

            // Usuario asignado
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_to_name')->nullable();
            $table->timestamp('assigned_at')->nullable();

            // Estado de la tarea (como Trello: todo, in_progress, review, done, cancelled)
            $table->string('status')->default('todo')->index();

            // Prioridad
            $table->enum('priority', ['baja', 'normal', 'alta', 'urgente'])->default('normal')->index();

            // Fechas importantes
            $table->timestamp('due_date')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('started_at')->nullable();

            // Campos adicionales
            $table->string('empresa')->nullable()->index();
            $table->string('sucursal')->nullable();
            $table->string('department')->nullable();
            $table->json('labels')->nullable(); // Etiquetas personalizables tipo Trello
            $table->integer('position')->default(0); // Para ordenamiento en la vista de tablero

            // Soft deletes para mantener historial
            $table->softDeletes();

            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index(['status', 'assigned_to']);
            $table->index(['status', 'created_by']);
            $table->index(['due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
