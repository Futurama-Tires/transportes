<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('calendario_verificacion', function (Blueprint $table) {
            // Columna para agrupar filas del calendario dentro de una misma "regla"
            $table->unsignedBigInteger('regla_id')->nullable()->after('id');
            $table->foreign('regla_id')
                  ->references('id')->on('verificacion_reglas')
                  ->nullOnDelete();
            $table->index('regla_id');
        });
    }

    public function down(): void
    {
        Schema::table('calendario_verificacion', function (Blueprint $table) {
            $table->dropForeign(['regla_id']);
            $table->dropIndex(['regla_id']);
            $table->dropColumn('regla_id');
        });
    }
};
