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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solar_system_id')->constrained()->onDelete('cascade');
            $table->foreignId('panel_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('production_date');
            $table->time('production_time')->nullable();
            $table->decimal('energy_produced_kwh', 10, 4);
            $table->decimal('energy_consumed_kwh', 12, 4)->default(0);
            $table->decimal('peak_power_kw', 12, 4)->nullable();
            $table->decimal('average_power_kw', 12, 4)->nullable();
            $table->decimal('irradiance_wm2', 8, 2)->nullable();
            $table->decimal('temperature_celsius', 5, 2)->nullable();
            $table->decimal('efficiency_percentage', 5, 2)->nullable();
            $table->enum('weather_condition', ['sunny', 'cloudy', 'rainy', 'partly_cloudy'])->nullable();
            $table->timestamps();

            $table->index(['solar_system_id', 'production_date']);
            $table->index(['panel_id', 'production_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
