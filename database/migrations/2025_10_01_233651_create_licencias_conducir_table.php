<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licencias_conducir', function (Blueprint $table) {
            $table->id();

            // Relación con Operador
            $table->foreignId('operador_id')
                  ->constrained('operadores')
                  ->cascadeOnDelete();

            // Datos de la licencia
            $table->enum('ambito', ['federal','estatal'])->nullable()->index(); // federal/estatal
            $table->string('tipo', 50)->nullable();       // Ej: "A", "B", "Chofer", "C"
            $table->string('folio', 50)->nullable()->unique(); // Número/folio de licencia (único). 
            // Si prevés folios repetidos entre ámbitos/estados, cambia a: $table->unique(['folio','ambito']);

            // Fechas
            $table->date('fecha_expedicion')->nullable();
            $table->date('fecha_vencimiento')->nullable()->index();

            // Emisor / Estado
            $table->string('emisor', 100)->nullable();        // Ej: "SCT", "Secretaría de Movilidad"
            $table->string('estado_emision', 100)->nullable(); // Ej: "Morelos", "CDMX"

            // Otros (opcional)
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licencias_conducir');
    }
};
