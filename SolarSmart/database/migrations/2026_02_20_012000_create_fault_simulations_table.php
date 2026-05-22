<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fault_simulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_id')->constrained()->onDelete('cascade');
            $table->foreignId('solar_system_id')->constrained()->onDelete('cascade');
            $table->enum('fault_type', [
                'inverter_failure',
                'panel_crack',
                'wiring_fault',
                'sensor_malfunction',
                'hot_spot',
                'delamination',
                'connection_failure',
                'soiling_severe',
                'shading_issue',
                'ground_fault',
            ]);
            $table->string('fault_description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('high');
            $table->enum('previous_status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('generated_alert_id')->nullable()->constrained('alerts')->onDelete('set null');
            $table->foreignId('generated_intervention_id')->nullable()->constrained('interventions')->onDelete('set null');
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('triggered_at')->useCurrent();
            $table->timestamps();

            $table->index(['panel_id', 'severity']);
            $table->index(['fault_type', 'severity']);
            $table->index(['solar_system_id', 'resolved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fault_simulations');
    }
};
