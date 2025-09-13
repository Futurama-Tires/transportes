<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            // Agrega las nuevas pólizas como cadenas opcionales (nullable)
            // Las colocamos después de poliza_hdi para mantener consistencia visual en el esquema
            $table->string('poliza_latino', 255)->nullable()->after('poliza_hdi');
            $table->string('poliza_qualitas', 255)->nullable()->after('poliza_latino');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn(['poliza_latino', 'poliza_qualitas']);
        });
    }
};
