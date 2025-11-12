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
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();

            // Relación con ticket
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');

            // Usuario que comenta
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('user_name')->nullable(); // Cache del nombre

            // Contenido del comentario
            $table->text('comment');

            // Tipo de comentario
            $table->enum('type', [
                'public',      // Visible para el usuario
                'internal',    // Solo para técnicos
                'solution',    // Solución al problema
                'status_change' // Cambio de estado automático
            ])->default('public');

            // Si es una nota privada (solo para staff)
            $table->boolean('is_private')->default(false);

            // Metadata
            $table->json('attachments')->nullable(); // Para futura implementación
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('ticket_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
    }
};
