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
        Schema::table('tickets', function (Blueprint $table) {
            // Agregar índices para optimizar búsqueda y ordenamiento
            $table->index('user_name');
            $table->index('title');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex(['user_name']);
            $table->dropIndex(['title']);
            $table->dropIndex(['updated_at']);
        });
    }
};
