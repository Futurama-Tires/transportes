<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comodin_gastos', function (Blueprint $table) {
            $table->id();

            // Relación con la tarjeta comodín
            $table->foreignId('tarjeta_comodin_id')
                  ->constrained('tarjetas_comodin')
                  ->cascadeOnDelete();

            // Datos básicos solicitados
            $table->date('fecha');                 // fecha del gasto
            $table->string('concepto');            // concepto del gasto
            $table->decimal('monto', 10, 2);       // monto del gasto

            $table->timestamps();

            // Índices útiles
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comodin_gastos');
    }
};
