<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->date('vencimiento_t_circulacion')->nullable()->change();
            $table->date('cambio_placas')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->string('vencimiento_t_circulacion')->nullable()->change();
            $table->string('cambio_placas')->nullable()->change();
        });
    }
};
