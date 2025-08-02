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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('ubicacion'); //Esto puede ser traido de una tabla a parte porque son varias ubicaciones y es escalable quizas
            $table->string('propietario')->nullable();
            $table->string('unidad'); // Ej. Tsuru1, Tsuru2, Ranger, etc.
            $table->string('marca')->nullable();
            $table->string('serie')->unique(); // Ej. WAUDF28P15A000598
            $table->string('motor')->nullable();
            $table->string('placa')->nullable()->unique();
            $table->string('estado')->nullable();
            $table->string('tarjeta_siVale')->nullable(); //Esto puede ser tabla a parte porque se repiten las tarjetas en varios vehiculos
            $table->string('nip')->nullable(); //Estpa asociado a una tarjeta
            $table->string('fec_vencimiento')->nullable();
            $table->string('vencimiento_t_circulacion')->nullable();
            $table->string('cambio_placas')->nullable();
            $table->string('poliza_hdi')->nullable();
            $table->float('rend')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
