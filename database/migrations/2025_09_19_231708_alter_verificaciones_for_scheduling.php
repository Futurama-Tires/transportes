<?php
// database/migrations/2025_09_19_000002_alter_verificaciones_for_scheduling.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('verificaciones', function (Blueprint $t) {
            // Programación como rango (ej. Ene 1 - Feb 28)
            $t->date('fecha_programada_inicio')->nullable()->after('comentarios');
            $t->date('fecha_programada_fin')->nullable()->after('fecha_programada_inicio');

            // Ejecución real
            $t->date('fecha_real')->nullable()->after('fecha_programada_fin');

            // Normalización del periodo
            $t->unsignedTinyInteger('mes_inicio')->nullable()->after('fecha_real');
            $t->unsignedTinyInteger('mes_fin')->nullable()->after('mes_inicio');
            $t->unsignedSmallInteger('anio')->nullable()->after('mes_fin');

            // Estado del trámite y holograma (cuando aplique)
            $t->enum('resultado', ['PENDIENTE','APROBADO','RECHAZADO','EXENTO','NO_APLICA'])
              ->default('PENDIENTE')->after('anio');
            $t->enum('holograma', ['00','0','1','2'])->nullable()->after('resultado');

            // Evidencia
            $t->string('comprobante_path', 255)->nullable()->after('holograma');

            // Regla utilizada (opcional)
            $t->foreignId('calendario_id')->nullable()
              ->constrained('calendario_verificacion')->nullOnDelete()->after('comprobante_path');

            // Evita duplicar una "tarea" del mismo periodo
            $t->unique(['vehiculo_id','anio','mes_inicio','mes_fin'], 'verif_unica_por_periodo');
        });

        // --- Migración de datos existentes ---
        // 1) Mover fecha_verificacion -> fecha_real (si existía)
        if (Schema::hasColumn('verificaciones', 'fecha_verificacion')) {
            DB::statement('UPDATE verificaciones SET fecha_real = fecha_verificacion WHERE fecha_real IS NULL AND fecha_verificacion IS NOT NULL');
        }

        // 2) Calcular bimestre y año a partir de fecha_real para historio (si existe)
        DB::statement("
            UPDATE verificaciones
            SET
              anio = IFNULL(anio, YEAR(fecha_real)),
              mes_inicio = CASE
                WHEN fecha_real IS NULL THEN mes_inicio
                WHEN MONTH(fecha_real) IN (1,2)  THEN 1
                WHEN MONTH(fecha_real) IN (3,4)  THEN 3
                WHEN MONTH(fecha_real) IN (5,6)  THEN 5
                WHEN MONTH(fecha_real) IN (7,8)  THEN 7
                WHEN MONTH(fecha_real) IN (9,10) THEN 9
                WHEN MONTH(fecha_real) IN (11,12) THEN 11
                ELSE mes_inicio END,
              mes_fin = CASE
                WHEN fecha_real IS NULL THEN mes_fin
                WHEN MONTH(fecha_real) IN (1,2)  THEN 2
                WHEN MONTH(fecha_real) IN (3,4)  THEN 4
                WHEN MONTH(fecha_real) IN (5,6)  THEN 6
                WHEN MONTH(fecha_real) IN (7,8)  THEN 8
                WHEN MONTH(fecha_real) IN (9,10) THEN 10
                WHEN MONTH(fecha_real) IN (11,12) THEN 12
                ELSE mes_fin END
        ");

        // (Opcional) Si quieres marcar como APROBADO todo lo que ya tenga fecha_real:
        // DB::statement(\"UPDATE verificaciones SET resultado='APROBADO' WHERE fecha_real IS NOT NULL AND resultado='PENDIENTE'\");
    }

    public function down(): void {
        Schema::table('verificaciones', function (Blueprint $t) {
            $t->dropUnique('verif_unica_por_periodo');
            $t->dropConstrainedForeignId('calendario_id');
            $t->dropColumn([
                'fecha_programada_inicio','fecha_programada_fin','fecha_real',
                'mes_inicio','mes_fin','anio','resultado','holograma','comprobante_path'
            ]);
        });
    }
};
