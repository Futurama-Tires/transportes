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
        Schema::create('cargas_combustible', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');                            // Columna A
            $table->string('mes');                            // Columna B
            $table->float('precio', 8, 2);                    // Columna C
            $table->enum('tipo_combustible', ['Magna', 'Diesel', 'Premium']); // Columna D
            $table->float('litros', 8, 3);                    // Columna E
            $table->float('total', 10, 2);                    // Columna F
            $table->string('custodio')->nullable();           // Columna G (no es FK)
            $table->foreignId('operador_id')->constrained('operadores')->onDelete('cascade'); // Columna H
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');   // Columna I + J
            $table->integer('km_inicial')->nullable();                    // Columna K
            $table->integer('km_final')->nullable();                      // Columna L
            $table->integer('recorrido')->nullable();                     // Columna M
            $table->float('rendimiento', 8, 2)->nullable();               // Columna N
            $table->float('diferencia', 8, 2)->nullable();                // Columna O
            $table->string('destino')->nullable();            // Columna P
            $table->text('observaciones')->nullable();        // Columna Q

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargas_combustible');
    }
};
