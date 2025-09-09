<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculo_fotos', function (Blueprint $table) {
            $table->id();

            // FK al vehículo; elimina las fotos si se elimina el vehículo
            $table->foreignId('vehiculo_id')
                  ->constrained('vehiculos')
                  ->cascadeOnDelete();

            // Ruta relativa del archivo en storage (p. ej. "vehiculos/abc123_foto1.jpg")
            $table->string('ruta', 2048);

            // Opcionales útiles
            $table->string('descripcion')->nullable();
            $table->unsignedInteger('orden')->default(0);

            $table->timestamps();

            $table->index(['vehiculo_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_fotos');
    }
};
