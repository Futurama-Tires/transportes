<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cargas_combustible', function (Blueprint $table) {
            // Estado de revisión
            $table->enum('estado', ['Pendiente', 'Aprobada'])
                  ->default('Pendiente');

            // Quién y cuándo se aprobó
            $table->foreignId('revisado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('revisado_en')->nullable();

            // Índice útil para bandejas/consultas
            $table->index(['estado', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::table('cargas_combustible', function (Blueprint $table) {
            // Elimina el índice compuesto (Laravel resuelve el nombre por columnas)
            $table->dropIndex(['estado', 'fecha']);

            // Elimina la FK y la columna revisado_por
            $table->dropConstrainedForeignId('revisado_por');

            // Elimina las demás columnas
            $table->dropColumn(['estado', 'revisado_en']);
        });
    }
};
