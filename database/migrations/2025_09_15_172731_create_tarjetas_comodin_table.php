<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarjetas_comodin', function (Blueprint $table) {
            $table->id();                                                // bigint unsigned, PK
            $table->string('numero_tarjeta');                            // varchar(255) NOT NULL
            $table->string('nip')->nullable();                           // varchar(255) NULL (igual que SiVale)
            $table->date('fecha_vencimiento')->nullable();               // date NULL
            $table->timestamps();                                        // created_at / updated_at (NULL por defecto)

            // Igual que en tarjetassivale: índice único sobre numero_tarjeta
            $table->unique('numero_tarjeta', 'tarjetas_comodin_numero_tarjeta_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarjetas_comodin');
    }
};
