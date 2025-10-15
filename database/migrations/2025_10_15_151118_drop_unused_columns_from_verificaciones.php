<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $table = 'verificaciones';
        $db    = DB::getDatabaseName();

        // 1) Asegura un índice simple sobre vehiculo_id para no romper la FK
        //    (la UNIQUE actual 'verif_unica_por_periodo' incluye vehiculo_id, pero la vamos a eliminar)
        $vehIdxName = 'verificaciones_vehiculo_id_index';
        $vehIdxExists = DB::table('information_schema.statistics')
            ->where('table_schema', $db)
            ->where('table_name', $table)
            ->where('index_name', $vehIdxName)
            ->exists();

        if (!$vehIdxExists) {
            Schema::table($table, function (Blueprint $t) use ($vehIdxName) {
                // Crear SIEMPRE un índice "dedicado" sólo a vehiculo_id
                $t->index('vehiculo_id', $vehIdxName);
            });
        }

        // 2) Elimina la UNIQUE que incluye columnas a borrar (si existe)
        $uniqName = 'verif_unica_por_periodo';
        $uniqExists = DB::table('information_schema.statistics')
            ->where('table_schema', $db)
            ->where('table_name', $table)
            ->where('index_name', $uniqName)
            ->exists();

        if ($uniqExists) {
            Schema::table($table, function (Blueprint $t) use ($uniqName) {
                $t->dropUnique($uniqName);
            });
        }

        // 3) Elimina columnas NO usadas, una por una y sólo si existen
        $colsToDrop = [
            'fecha_programada_inicio', // por si existe en alguna máquina
            'fecha_programada_fin',    // por si existe en alguna máquina
            'mes_inicio',
            'mes_fin',
            'anio',
        ];

        foreach ($colsToDrop as $col) {
            if (Schema::hasColumn($table, $col)) {
                Schema::table($table, function (Blueprint $t) use ($col) {
                    $t->dropColumn($col);
                });
            }
        }
    }

    public function down(): void
    {
        $table = 'verificaciones';
        $db    = DB::getDatabaseName();

        // 1) Restaurar columnas (todas como NULL para rollback seguro)
        Schema::table($table, function (Blueprint $t) {
            if (!Schema::hasColumn('verificaciones', 'fecha_programada_inicio')) {
                $t->date('fecha_programada_inicio')->nullable();
            }
            if (!Schema::hasColumn('verificaciones', 'fecha_programada_fin')) {
                $t->date('fecha_programada_fin')->nullable();
            }
            if (!Schema::hasColumn('verificaciones', 'mes_inicio')) {
                $t->unsignedTinyInteger('mes_inicio')->nullable();
            }
            if (!Schema::hasColumn('verificaciones', 'mes_fin')) {
                $t->unsignedTinyInteger('mes_fin')->nullable();
            }
            if (!Schema::hasColumn('verificaciones', 'anio')) {
                $t->unsignedSmallInteger('anio')->nullable();
            }
        });

        // 2) Re-crear la UNIQUE original (si no existe)
        $uniqName = 'verif_unica_por_periodo';
        $uniqExists = DB::table('information_schema.statistics')
            ->where('table_schema', $db)
            ->where('table_name', $table)
            ->where('index_name', $uniqName)
            ->exists();

        if (!$uniqExists) {
            Schema::table($table, function (Blueprint $t) use ($uniqName) {
                $t->unique(['vehiculo_id', 'anio', 'mes_inicio', 'mes_fin'], $uniqName);
            });
        }

        // 3) (Opcional) Dejar el índice simple de vehiculo_id; no estorba.
        //    Si quisieras quitarlo en rollback:
        // Schema::table($table, function (Blueprint $t) {
        //     $t->dropIndex('verificaciones_vehiculo_id_index');
        // });
    }
};
