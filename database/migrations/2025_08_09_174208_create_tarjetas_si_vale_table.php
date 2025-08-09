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
        Schema::create('tarjetasSiVale', function (Blueprint $table) {
            $table->id();
            $table->string('numero_tarjeta')->unique();
            $table->string('nip')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjetasSiVale');
    }
};
