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
        Schema::create('tanques', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');

            $table->unsignedTinyInteger('numero_tanque')->default(1); // Ej. 1, 2, etc.
            $table->float('capacidad_litros', 8, 2);                  // Capacidad individual del tanque
            $table->float('rendimiento_estimado', 8, 2)->nullable(); // Manual
            $table->float('costo_tanque_lleno', 10, 2)->nullable();  // Aprox. si se llena completo
            $table->enum('tipo_combustible', ['Magna', 'Diesel', 'Premium']);

            $table->timestamps();

            // Previene duplicación de número de tanque por vehículo
            $table->unique(['vehiculo_id', 'numero_tanque']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tanques');
    }
};
