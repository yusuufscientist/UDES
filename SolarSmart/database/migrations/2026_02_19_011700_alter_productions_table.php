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
        Schema::table('productions', function (Blueprint $table) {
            $table->decimal('peak_power_kw', 12, 4)->nullable()->change();
            $table->decimal('average_power_kw', 12, 4)->nullable()->change();
            $table->decimal('energy_consumed_kwh', 12, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->decimal('peak_power_kw', 8, 4)->change();
            $table->decimal('average_power_kw', 8, 4)->change();
            $table->decimal('energy_consumed_kwh', 10, 4)->change();
        });
    }
};
