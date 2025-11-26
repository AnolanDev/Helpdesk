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
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_name')->nullable(); // Cached user name
            $table->enum('activity_type', [
                'created',
                'assigned',
                'reassigned',
                'status_changed',
                'priority_changed',
                'category_changed',
                'commented',
                'resolved',
                'closed',
                'reopened',
                'updated',
            ]);
            $table->text('description'); // Human-readable description
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->json('changes')->nullable(); // Detailed changes as JSON
            $table->timestamps();

            // Indexes for performance
            $table->index(['ticket_id', 'created_at']);
            $table->index('activity_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
