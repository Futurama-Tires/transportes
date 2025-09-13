<?php

// database/migrations/2025_09_13_000001_add_kilometros_to_vehiculos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            // odómetro actual del vehículo (lectura más reciente)
            $table->unsignedInteger('kilometros')->nullable()->after('poliza_hdi');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn('kilometros');
        });
    }
};
