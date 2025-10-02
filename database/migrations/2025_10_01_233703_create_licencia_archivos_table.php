<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licencia_archivos', function (Blueprint $table) {
            $table->id();

            // Relación con Licencia
            $table->foreignId('licencia_id')
                  ->constrained('licencias_conducir')
                  ->cascadeOnDelete();

            // Metadatos del archivo privado (p.ej. storage/app/private/licencias/{id}/...)
            $table->string('nombre_original');  // Nombre real del archivo subido
            $table->string('ruta');             // Ruta relativa en el DISK (ej. "licencias/123/20250101_UUID.pdf")
            $table->string('mime', 50)->nullable(); // application/pdf, image/jpeg, etc.
            $table->unsignedBigInteger('size')->nullable();   // bytes
            $table->unsignedInteger('orden')->default(0);     // para ordenar si hay varios

            $table->timestamps();

            $table->index(['licencia_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licencia_archivos');
    }
};
