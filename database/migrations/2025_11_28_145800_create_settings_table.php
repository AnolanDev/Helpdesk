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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('group')->default('general'); // general, sla, notifications, etc.
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();

            // Índices
            $table->index('key');
            $table->index('group');
        });

        // Insertar valores por defecto de SLA
        DB::table('settings')->insert([
            [
                'key' => 'sla_urgent_hours',
                'value' => '4',
                'type' => 'integer',
                'group' => 'sla',
                'label' => 'SLA - Urgente (horas)',
                'description' => 'Tiempo máximo de resolución para tickets urgentes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sla_high_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'sla',
                'label' => 'SLA - Alta (horas)',
                'description' => 'Tiempo máximo de resolución para tickets de prioridad alta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sla_normal_hours',
                'value' => '72',
                'type' => 'integer',
                'group' => 'sla',
                'label' => 'SLA - Normal (horas)',
                'description' => 'Tiempo máximo de resolución para tickets de prioridad normal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sla_low_hours',
                'value' => '168',
                'type' => 'integer',
                'group' => 'sla',
                'label' => 'SLA - Baja (horas)',
                'description' => 'Tiempo máximo de resolución para tickets de prioridad baja',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sla_warning_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'sla',
                'label' => 'Advertencia de vencimiento (horas)',
                'description' => 'Mostrar advertencia cuando falten X horas para el vencimiento',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
