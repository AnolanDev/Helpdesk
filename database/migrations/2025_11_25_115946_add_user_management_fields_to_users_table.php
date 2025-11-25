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
        Schema::table('users', function (Blueprint $table) {
            // Tipo de usuario para el sistema de helpdesk
            $table->enum('tipo_usuario', ['usuario_final', 'tech', 'admin'])
                ->default('usuario_final')
                ->after('is_active');

            // Campos de organización adicionales (independientes de GLPI)
            $table->string('sucursal')->nullable()->after('tipo_usuario');
            $table->string('empresa')->nullable()->after('sucursal');

            // Índice para búsqueda por tipo
            $table->index('tipo_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tipo_usuario']);
            $table->dropColumn(['tipo_usuario', 'sucursal', 'empresa']);
        });
    }
};
