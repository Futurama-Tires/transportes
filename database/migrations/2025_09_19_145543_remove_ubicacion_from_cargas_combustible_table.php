<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Quita la columna 'ubicacion' de cargas_combustible.
     */
    public function up(): void
    {
        // Por seguridad, solo la quitamos si existe
        if (Schema::hasColumn('cargas_combustible', 'ubicacion')) {
            Schema::table('cargas_combustible', function (Blueprint $table) {
                $table->dropColumn('ubicacion');
            });
        }
    }

    /**
     * Vuelve a agregar la columna 'ubicacion' (rollback).
     * Tipo y nulabilidad según el esquema actual.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('cargas_combustible', 'ubicacion')) {
            Schema::table('cargas_combustible', function (Blueprint $table) {
                // En el dump está como varchar(255) nullable, sin índice ni FK
                $table->string('ubicacion', 255)->nullable()->after('id');
            });
        }
    }
};
