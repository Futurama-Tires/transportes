<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verificacion_regla_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regla_id')
                  ->constrained('verificacion_reglas')
                  ->onDelete('cascade');

            // Frecuencia de la regla (Semestral/Anual), útil para validar la UI
            $table->enum('frecuencia', ['Semestral', 'Anual']);

            // Terminación 0..9
            $table->unsignedTinyInteger('terminacion'); // 0..9

            // 0 = anual, 1 o 2 = semestre
            $table->unsignedTinyInteger('semestre')->default(0);

            // Meses (1..12)
            $table->unsignedTinyInteger('mes_inicio'); // 1..12
            $table->unsignedTinyInteger('mes_fin');    // 1..12

            $table->timestamps();

            // Evitar duplicados por regla/terminación/semestre
            $table->unique(['regla_id', 'terminacion', 'semestre'], 'uq_regla_terminacion_sem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verificacion_regla_detalles');
    }
};
