<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verificacion_reglas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                   // Ej: "CAMe 2025"
            $table->string('version')->nullable();      // Ej: "2025" o "2025-S1"
            $table->enum('status', ['draft','published','archived'])->default('draft');
            $table->date('vigencia_inicio')->nullable();// Opcional: activa por rango
            $table->date('vigencia_fin')->nullable();   // Opcional
            $table->enum('frecuencia', ['Semestral','Anual'])->default('Semestral');
            $table->json('estados')->nullable();        // Lista JSON de estados aplicables (para el UI)
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['nombre','version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verificacion_reglas');
    }
};
