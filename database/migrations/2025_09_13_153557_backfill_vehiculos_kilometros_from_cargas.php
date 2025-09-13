<?php

// database/migrations/2025_09_13_000002_backfill_vehiculos_kilometros_from_cargas.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Para cada vehículo, toma el km_final más reciente (por fecha e id) y úsalo como odómetro actual
        DB::statement("
            UPDATE vehiculos v
            LEFT JOIN (
                SELECT c1.vehiculo_id, c1.km_final
                FROM cargas_combustible c1
                INNER JOIN (
                    SELECT vehiculo_id, MAX(CONCAT(fecha,'|',LPAD(id,10,'0'))) AS max_key
                    FROM cargas_combustible
                    GROUP BY vehiculo_id
                ) last ON last.vehiculo_id = c1.vehiculo_id
                AND CONCAT(c1.fecha,'|',LPAD(c1.id,10,'0')) = last.max_key
            ) ult ON ult.vehiculo_id = v.id
            SET v.kilometros = ult.km_final
            WHERE ult.km_final IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("UPDATE vehiculos SET kilometros = NULL");
    }
};

