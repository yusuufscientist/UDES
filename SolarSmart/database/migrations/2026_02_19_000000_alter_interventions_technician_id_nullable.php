<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->unsignedBigInteger('technician_id')->nullable()->change();
            $table->foreign('technician_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->unsignedBigInteger('technician_id')->nullable(false)->change();
            $table->foreign('technician_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};