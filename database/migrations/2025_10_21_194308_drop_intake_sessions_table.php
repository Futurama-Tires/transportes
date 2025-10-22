<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina la tabla intake_sessions.
     */
    public function up(): void
    {
        Schema::dropIfExists('intake_sessions');
    }

    /**
     * Rollback: vuelve a crear la tabla tal como estaba.
     * (Sin los CHECK JSON para máxima compatibilidad.)
     */
    public function down(): void
    {
        Schema::create('intake_sessions', function (Blueprint $table) {
            $table->char('id', 36);
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->foreignId('operador_id')->constrained('operadores');

            $table->enum('status', [
                'TICKET_PENDING',
                'TICKET_READY',
                'VOUCHER_PENDING',
                'VOUCHER_MISMATCH',
                'VOUCHER_READY',
                'ODOMETER_PENDING',
                'ODOMETER_READY',
                'REVIEW',
                'COMPLETED',
                'CANCELLED',
            ])->default('TICKET_PENDING');

            // Campos JSON (sin CHECK json_valid para compatibilidad amplia)
            $table->longText('images')->nullable();
            $table->longText('ticket_json')->nullable();
            $table->longText('voucher_json')->nullable();
            $table->longText('odometro_json')->nullable();

            $table->timestamps();

            $table->primary('id');
        });
    }
};
