<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operador_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operador_id')
                  ->constrained('operadores')
                  ->cascadeOnDelete(); // al borrar operador, borra filas hijas
            $table->string('ruta', 2048);     // p. ej. operadores/21/20250915_120101_21_uuid.jpg
            $table->string('descripcion')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['operador_id', 'orden']);
            $table->index(['operador_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operador_fotos');
    }
};
