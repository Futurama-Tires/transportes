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
        Schema::table('cargas_combustible', function (Blueprint $table) {
            // La coloco después de 'destino' que ya existe en tu tabla
            $table->string('ubicacion', 255)->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargas_combustible', function (Blueprint $table) {
            $table->dropColumn('ubicacion');
        });
    }
};
