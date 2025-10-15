<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /* ===== Helpers ===== */

    private function normalizeEstado(?string $s): string
    {
        $norm = Str::of($s ?? '')
            ->ascii()
            ->upper()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        if (in_array($norm, ['ESTADO DE MEXICO','MEXICO','EDO MEXICO','EDO. MEX','E DOMEX'], true)) {
            return 'EDO MEX';
        }
        return $norm;
    }

    private function columnExists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    private function columnHasIndex(string $table, string $column): bool
    {
        $sql = "SELECT 1 FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1";
        return !empty(DB::select($sql, [$table, $column]));
    }

    /** Nombres de FKs actuales sobre una columna dada. */
    private function fkConstraintsOnColumn(string $table, string $column): array
    {
        $sql = "SELECT CONSTRAINT_NAME
                  FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME IS NOT NULL";
        return array_map(fn($o) => $o->CONSTRAINT_NAME, DB::select($sql, [$table, $column]));
    }

    /** Intenta enlazar verificaciones antiguas con su periodo calendario (si hay datos suficientes). */
    private function backfillCalendarioId(): void
    {
        if (!Schema::hasTable('verificaciones') || !Schema::hasTable('calendario_verificacion')) return;

        $tieneAnio = Schema::hasColumn('verificaciones', 'anio');

        DB::table('verificaciones')
            ->whereNull('calendario_id')
            ->when($tieneAnio, fn($q) => $q->whereNotNull('anio'))
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($tieneAnio) {
                foreach ($rows as $r) {
                    $veh = DB::table('vehiculos')->select('placa', 'estado')->where('id', $r->vehiculo_id)->first();

                    $estadoRaw = $r->estado ?? ($veh->estado ?? null);
                    if (!$estadoRaw) continue;
                    $estado = $this->normalizeEstado($estadoRaw);

                    $ini = $r->fecha_programada_inicio ?? $r->fecha_verificacion ?? null;
                    $fin = $r->fecha_programada_fin    ?? $r->fecha_verificacion ?? null;
                    if (!$ini || !$fin) continue;

                    $anio = $tieneAnio && isset($r->anio)
                        ? (int) $r->anio
                        : (int) date('Y', strtotime($ini));

                    // terminación (si hay placa)
                    $term = null;
                    if ($veh && $veh->placa) {
                        if (preg_match('/.*?(\d)(?!.*\d)/u', preg_replace('/[\s\-]/', '', mb_strtoupper($veh->placa)), $m)) {
                            $term = (int) $m[1];
                        }
                    }

                    $mesIni = (int) date('n', strtotime($ini));
                    $mesFin = (int) date('n', strtotime($fin));

                    // 1) Match exacto por meses
                    $cv = DB::table('calendario_verificacion')
                        ->where('anio', $anio)
                        ->where('estado', $estado)
                        ->when($term !== null, fn($q) => $q->where('terminacion', $term))
                        ->where('mes_inicio', $mesIni)
                        ->where('mes_fin', $mesFin)
                        ->orderBy('id')
                        ->first();

                    // 2) Match por solapamiento de fechas
                    if (!$cv) {
                        $cv = DB::table('calendario_verificacion')
                            ->where('anio', $anio)
                            ->where('estado', $estado)
                            ->when($term !== null, fn($q) => $q->where('terminacion', $term))
                            ->whereDate('vigente_desde', '<=', date('Y-m-d', strtotime($fin)))
                            ->whereDate('vigente_hasta', '>=', date('Y-m-d', strtotime($ini)))
                            ->orderBy('id')
                            ->first();
                    }

                    if ($cv) {
                        DB::table('verificaciones')->where('id', $r->id)->update(['calendario_id' => $cv->id]);
                    }
                }
            });
    }

    public function up(): void
    {
        if (!Schema::hasTable('verificaciones')) return;

        // 1) Agregar columna (sin AFTER)
        if (!$this->columnExists('verificaciones', 'calendario_id')) {
            Schema::table('verificaciones', function (Blueprint $t) {
                $t->unsignedBigInteger('calendario_id')->nullable();
            });
        }

        // 2) Índice si no existe
        if (!$this->columnHasIndex('verificaciones', 'calendario_id')) {
            Schema::table('verificaciones', function (Blueprint $t) {
                $t->index('calendario_id', 'verificaciones_calendario_id_index');
            });
        }

        // 3) FK (quita cualquier FK previa y crea una con ON DELETE SET NULL)
        foreach ($this->fkConstraintsOnColumn('verificaciones', 'calendario_id') as $fk) {
            try { DB::statement("ALTER TABLE `verificaciones` DROP FOREIGN KEY `{$fk}`"); } catch (\Throwable $e) {}
        }
        try {
            DB::statement(
                "ALTER TABLE `verificaciones`
                 ADD CONSTRAINT `verif_calendario_fk`
                 FOREIGN KEY (`calendario_id`) REFERENCES `calendario_verificacion`(`id`) ON DELETE SET NULL"
            );
        } catch (\Throwable $e) {
            // si ya existe con otro nombre, ignoramos
        }

        // 4) Backfill
        $this->backfillCalendarioId();
    }

    public function down(): void
    {
        if (!Schema::hasTable('verificaciones')) return;

        // Quitar FK si la pusimos
        $fks = $this->fkConstraintsOnColumn('verificaciones', 'calendario_id');
        if (in_array('verif_calendario_fk', $fks, true)) {
            try { DB::statement("ALTER TABLE `verificaciones` DROP FOREIGN KEY `verif_calendario_fk`"); } catch (\Throwable $e) {}
        }

        // Quitar índice
        try {
            Schema::table('verificaciones', function (Blueprint $t) {
                $t->dropIndex('verificaciones_calendario_id_index');
            });
        } catch (\Throwable $e) {}

        // Quitar columna
        if ($this->columnExists('verificaciones', 'calendario_id')) {
            Schema::table('verificaciones', function (Blueprint $t) {
                $t->dropColumn('calendario_id');
            });
        }
    }
};
