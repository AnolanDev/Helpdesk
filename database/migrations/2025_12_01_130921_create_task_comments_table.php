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
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();

            // Relación con la tarea
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();

            // Usuario que comenta
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();

            // Contenido del comentario
            $table->text('comment');

            // Tipo de comentario
            $table->enum('type', ['comment', 'status_change', 'system'])->default('comment');

            // Soft deletes
            $table->softDeletes();

            $table->timestamps();

            // Índices
            $table->index(['task_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_comments');
    }
};
