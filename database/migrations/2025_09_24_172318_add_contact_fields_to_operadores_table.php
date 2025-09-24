<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('operadores', function (Blueprint $table) {
            // Teléfonos suelen caber en 20 chars con extensión o +código país
            $table->string('telefono', 20)->nullable();
            $table->string('contacto_emergencia_nombre')->nullable();
            $table->string('contacto_emergencia_tel', 20)->nullable();
            // Tipo de sangre: A+, O-, etc. 5 chars es suficiente
            $table->string('tipo_sangre', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('operadores', function (Blueprint $table) {
            $table->dropColumn([
                'telefono',
                'contacto_emergencia_nombre',
                'contacto_emergencia_tel',
                'tipo_sangre',
            ]);
        });
    }
};
