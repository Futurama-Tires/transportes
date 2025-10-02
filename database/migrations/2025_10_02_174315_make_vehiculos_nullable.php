<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            // Ajusta 255 si tus columnas tenían otra longitud
            $table->string('ubicacion', 255)->nullable()->change();
            $table->string('propietario', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Si existen NULL, conviértelos antes de volver a NOT NULL
        DB::table('vehiculos')->whereNull('ubicacion')->update(['ubicacion' => '']);
        DB::table('vehiculos')->whereNull('propietario')->update(['propietario' => '']);

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->string('ubicacion', 255)->nullable(false)->change();
            $table->string('propietario', 255)->nullable(false)->change();
        });
    }
};
