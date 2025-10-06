<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operadores', function (Blueprint $table) {
            // Se coloca después de RFC para mantener el orden lógico de datos personales
            $table->string('domicilio')->nullable()->after('rfc');
        });
    }

    public function down(): void
    {
        Schema::table('operadores', function (Blueprint $table) {
            $table->dropColumn('domicilio');
        });
    }
};
