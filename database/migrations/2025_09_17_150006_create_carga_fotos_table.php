<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carga_fotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carga_id');  // FK a cargas_combustible
            $table->string('tipo', 32)->nullable();  // 'ticket' | 'voucher' | 'odometro' | 'extra'
            $table->string('path');                  // ruta relativa en el disco (public)
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable(); // bytes
            $table->string('original_name', 255)->nullable();
            $table->timestamps();

            $table->index('carga_id');
            $table->foreign('carga_id')
                ->references('id')->on('cargas_combustible')
                ->onDelete('cascade'); // si borras la carga, borras sus fotos
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carga_fotos');
    }
};
