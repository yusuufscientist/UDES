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
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solar_system_id')->constrained()->onDelete('cascade');
            $table->foreignId('panel_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('alert_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['routine_maintenance', 'repair', 'inspection', 'cleaning', 'emergency_repair']);
            $table->text('description');
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->decimal('cost', 10, 2)->nullable();
            $table->text('parts_replaced')->nullable();
            $table->text('notes')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->timestamps();

            $table->index(['technician_id', 'status']);
            $table->index(['solar_system_id', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
