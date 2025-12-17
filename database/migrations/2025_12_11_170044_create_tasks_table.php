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
            $table->foreignId('board_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('creada'); // CREADA, ANALIZADA, PROGRAMADA, EN_PROGRESO, FINALIZADA, CANCELADA
            $table->string('priority')->default('normal'); // BAJA, NORMAL, ALTA, URGENTE
            $table->text('cancel_reason')->nullable(); // Documentación obligatoria al cancelar
            $table->timestamp('completed_at')->nullable(); // Solo cuando se marca FINALIZADA
            $table->integer('progress')->default(0); // Porcentaje de avance (0-100)
            $table->boolean('is_overdue')->default(false); // Flag para tareas vencidas
            $table->timestamp('overdue_notified_at')->nullable(); // Última vez que se notificó que está vencida
            $table->timestamps();
            $table->softDeletes();

            $table->index('board_id');
            $table->index('status');
            $table->index('priority');
            $table->index('is_overdue');
            $table->index('created_at');
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
