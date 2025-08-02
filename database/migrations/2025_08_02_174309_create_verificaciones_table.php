<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('verificaciones', function (Blueprint $table) {
            $table->id();

            // Relación con vehículo
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');

            $table->string('estado');                 // Morelos, etc
            $table->text('comentarios')->nullable();  // Comentarios opcionales
            $table->date('fecha_verificacion');       // Fecha exacta de la verificación

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verificaciones');
    }
};
