<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade el campo 'descripcion' a:
     *  - tarjetassivale
     *  - tarjetas_comodin
     */
    public function up(): void
    {
        // tarjetassivale
        if (!Schema::hasColumn('tarjetassivale', 'descripcion')) {
            Schema::table('tarjetassivale', function (Blueprint $table) {
                $table->text('descripcion')->nullable()->after('fecha_vencimiento');
            });
        }

        // tarjetas_comodin
        if (!Schema::hasColumn('tarjetas_comodin', 'descripcion')) {
            Schema::table('tarjetas_comodin', function (Blueprint $table) {
                $table->text('descripcion')->nullable()->after('fecha_vencimiento');
            });
        }
    }

    /**
     * Reversa los cambios.
     */
    public function down(): void
    {
        // tarjetassivale
        if (Schema::hasColumn('tarjetassivale', 'descripcion')) {
            Schema::table('tarjetassivale', function (Blueprint $table) {
                $table->dropColumn('descripcion');
            });
        }

        // tarjetas_comodin
        if (Schema::hasColumn('tarjetas_comodin', 'descripcion')) {
            Schema::table('tarjetas_comodin', function (Blueprint $table) {
                $table->dropColumn('descripcion');
            });
        }
    }
};
