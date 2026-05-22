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
        Schema::create('panels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solar_system_id')->constrained()->onDelete('cascade');
            $table->string('serial_number')->unique();
            $table->string('model');
            $table->string('manufacturer');
            $table->decimal('capacity_watts', 8, 2);
            $table->decimal('efficiency_rating', 5, 2)->nullable();
            $table->date('installation_date');
            $table->enum('status', ['active', 'inactive', 'faulty', 'maintenance'])->default('active');
            $table->decimal('current_voltage', 8, 2)->nullable();
            $table->decimal('current_amperage', 8, 2)->nullable();
            $table->decimal('current_power_output', 8, 2)->nullable();
            $table->timestamp('last_reading_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panels');
    }
};
