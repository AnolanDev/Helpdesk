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
            // ID de GLPI para relacionar el usuario
            $table->unsignedBigInteger('glpi_id')->nullable()->unique()->after('id');

            // Campos adicionales de GLPI
            $table->string('username')->nullable()->after('name');
            $table->string('firstname')->nullable()->after('username');
            $table->string('realname')->nullable()->after('firstname');
            $table->string('phone')->nullable()->after('email');
            $table->string('phone2')->nullable()->after('phone');
            $table->string('mobile')->nullable()->after('phone2');

            // Ubicación y entidad
            $table->unsignedBigInteger('glpi_entity_id')->nullable();
            $table->string('entity_name')->nullable();
            $table->unsignedBigInteger('glpi_location_id')->nullable();
            $table->string('location_name')->nullable();

            // Perfiles y grupos (JSON para flexibilidad)
            $table->json('glpi_profiles')->nullable();
            $table->json('glpi_groups')->nullable();

            // Estado y sincronización
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status')->default('pending'); // pending, synced, error

            // Índices para búsqueda rápida
            $table->index('glpi_id');
            $table->index('username');
            $table->index('is_active');
            $table->index('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['glpi_id']);
            $table->dropIndex(['username']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['last_synced_at']);

            $table->dropColumn([
                'glpi_id',
                'username',
                'firstname',
                'realname',
                'phone',
                'phone2',
                'mobile',
                'glpi_entity_id',
                'entity_name',
                'glpi_location_id',
                'location_name',
                'glpi_profiles',
                'glpi_groups',
                'is_active',
                'last_synced_at',
                'sync_status',
            ]);
        });
    }
};
