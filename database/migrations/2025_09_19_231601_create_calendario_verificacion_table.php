<?php
// database/migrations/2025_09_19_000001_create_calendario_verificacion_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('calendario_verificacion', function (Blueprint $t) {
            $t->id();
            $t->string('estado', 50);                // EDO MEX, MORELOS, JALISCO, FEDERAL, etc.
            $t->unsignedTinyInteger('terminacion');  // 0..9 (último dígito)
            $t->unsignedTinyInteger('mes_inicio');   // 1..12
            $t->unsignedTinyInteger('mes_fin');      // 1..12
            $t->unsignedTinyInteger('semestre')->nullable(); // 1,2 o null (anual)
            $t->enum('frecuencia', ['Semestral','Anual']);
            $t->unsignedSmallInteger('anio')->nullable();    // null = regla base
            $t->date('vigente_desde')->nullable();
            $t->date('vigente_hasta')->nullable();
            $t->timestamps();

            $t->unique(['estado','terminacion','mes_inicio','mes_fin','anio'], 'cal_verif_regla_unica');
            $t->index(['estado','terminacion']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('calendario_verificacion');
    }
};
