<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verificacion_regla_estados', function (Blueprint $table) {
            $table->id();

            $table->foreignId('regla_id')
                  ->constrained('verificacion_reglas')
                  ->onDelete('cascade');

            $table->unsignedSmallInteger('anio');

            // Estado NORMALIZADO (MAYÚSCULAS sin acentos; caso especial: EDO MEX)
            $table->string('estado', 100);

            $table->timestamps();

            // Único por anio+estado (impide que otra regla use el mismo estado ese año)
            $table->unique(['anio', 'estado'], 'uq_anio_estado');

            // Para consultas
            $table->index(['regla_id', 'anio'], 'ix_regla_anio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verificacion_regla_estados');
    }
};
