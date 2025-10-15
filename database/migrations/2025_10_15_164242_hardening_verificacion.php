<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /* ===================== Helpers ===================== */

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

    /** ¿Existe cualquier índice (de cualquier nombre) sobre esta columna? */
    private function columnHasIndex(string $table, string $column): bool
    {
        $sql = "SELECT 1
                  FROM information_schema.statistics
                 WHERE table_schema = DATABASE()
                   AND table_name = ?
                   AND column_name = ?
                 LIMIT 1";
        return !empty(DB::select($sql, [$table, $column]));
    }

    /** ¿Existe algún UNIQUE (cualquier nombre) exactamente sobre estas columnas y en este orden? */
    private function uniqueExistsByColumns(string $table, array $columns): bool
    {
        $sql = "SELECT INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX, NON_UNIQUE
                  FROM information_schema.statistics
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                 ORDER BY INDEX_NAME, SEQ_IN_INDEX";
        $rows = DB::select($sql, [$table]);

        $uniques = [];
        foreach ($rows as $r) {
            if ((int)$r->NON_UNIQUE === 0) {
                $uniques[$r->INDEX_NAME][$r->SEQ_IN_INDEX] = $r->COLUMN_NAME;
            }
        }
        foreach ($uniques as $colsBySeq) {
            ksort($colsBySeq);
            $cols = array_values($colsBySeq);
            if ($cols === array_values($columns)) return true;
        }
        return false;
    }

    /** Nombres de FKs actuales sobre regla_id (cualquiera que sea su nombre). */
    private function fkConstraintsOnReglaId(string $table): array
    {
        $sql = "SELECT CONSTRAINT_NAME
                  FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = 'regla_id'
                   AND REFERENCED_TABLE_NAME IS NOT NULL";
        return array_map(fn($o) => $o->CONSTRAINT_NAME, DB::select($sql, [$table]));
    }

    private function normalizeEstadosData(): void
    {
        if (Schema::hasTable('verificacion_regla_estados')) {
            foreach (DB::table('verificacion_regla_estados')->select('id','estado')->get() as $row) {
                $norm = $this->normalizeEstado($row->estado);
                if ($norm !== $row->estado) {
                    DB::table('verificacion_regla_estados')->where('id', $row->id)->update(['estado' => $norm]);
                }
            }
        }
        if (Schema::hasTable('calendario_verificacion')) {
            foreach (DB::table('calendario_verificacion')->select('id','estado')->get() as $row) {
                $norm = $this->normalizeEstado($row->estado);
                if ($norm !== $row->estado) {
                    DB::table('calendario_verificacion')->where('id', $row->id)->update(['estado' => $norm]);
                }
            }
        }
    }

    private function dedupePivotEstados(): void
    {
        if (!Schema::hasTable('verificacion_regla_estados')) return;

        $rows = DB::table('verificacion_regla_estados')
            ->select('id','anio','estado')
            ->orderBy('id')
            ->get();

        $groups = [];
        foreach ($rows as $r) {
            $k = (int)$r->anio.'|'.$this->normalizeEstado($r->estado);
            $groups[$k][] = (int)$r->id;
        }

        $idsToDelete = [];
        foreach ($groups as $ids) {
            if (count($ids) > 1) {
                sort($ids);
                array_shift($ids); // conserva el menor id
                $idsToDelete = array_merge($idsToDelete, $ids);
            }
        }

        if (!empty($idsToDelete)) {
            DB::table('verificacion_regla_estados')->whereIn('id', $idsToDelete)->delete();
        }
    }

    private function dedupeCalendario(): void
    {
        if (!Schema::hasTable('calendario_verificacion')) return;

        $rows = DB::table('calendario_verificacion')
            ->select('id','estado','terminacion','mes_inicio','mes_fin','anio')
            ->orderBy('id')
            ->get();

        $groups = [];
        foreach ($rows as $r) {
            $k = implode('|', [
                $this->normalizeEstado($r->estado),
                (int)$r->terminacion,
                (int)$r->mes_inicio,
                (int)$r->mes_fin,
                (int)$r->anio,
            ]);
            $groups[$k][] = (int)$r->id;
        }

        $idsToDelete = [];
        foreach ($groups as $ids) {
            if (count($ids) > 1) {
                sort($ids);
                array_shift($ids);
                $idsToDelete = array_merge($idsToDelete, $ids);
            }
        }

        if (!empty($idsToDelete)) {
            DB::table('calendario_verificacion')->whereIn('id', $idsToDelete)->delete();
        }
    }

    /* ===================== up / down ===================== */

    public function up(): void
    {
        // 0) Normaliza y deduplica antes de imponer UNIQUEs
        $this->normalizeEstadosData();
        $this->dedupePivotEstados();
        $this->dedupeCalendario();

        // 1) UNIQUE (anio, estado) en verificacion_regla_estados (si no existe)
        if (Schema::hasTable('verificacion_regla_estados')) {
            $uniqCols = ['anio','estado'];
            if (!$this->uniqueExistsByColumns('verificacion_regla_estados', $uniqCols)) {
                Schema::table('verificacion_regla_estados', function (Blueprint $t) {
                    $t->unique(['anio','estado'], 'vre_anio_estado_uniq');
                });
            }

            // FK regla_id con ON DELETE CASCADE
            if (Schema::hasColumn('verificacion_regla_estados','regla_id') &&
                Schema::hasTable('verificacion_reglas')) {

                // Índice en regla_id sólo si NO existe (evita el error 1061)
                if (!$this->columnHasIndex('verificacion_regla_estados', 'regla_id')) {
                    Schema::table('verificacion_regla_estados', function (Blueprint $t) {
                        $t->index('regla_id');
                    });
                }

                // Quita cualquier FK previa y agrega una con nombre estable
                foreach ($this->fkConstraintsOnReglaId('verificacion_regla_estados') as $fk) {
                    try { DB::statement("ALTER TABLE `verificacion_regla_estados` DROP FOREIGN KEY `{$fk}`"); } catch (\Throwable $e) {}
                }
                try {
                    DB::statement(
                        "ALTER TABLE `verificacion_regla_estados`
                         ADD CONSTRAINT `vre_regla_fk`
                         FOREIGN KEY (`regla_id`) REFERENCES `verificacion_reglas`(`id`) ON DELETE CASCADE"
                    );
                } catch (\Throwable $e) {}
            }
        }

        // 2) FK cascada en verificacion_regla_detalles
        if (Schema::hasTable('verificacion_regla_detalles') &&
            Schema::hasColumn('verificacion_regla_detalles','regla_id') &&
            Schema::hasTable('verificacion_reglas')) {

            if (!$this->columnHasIndex('verificacion_regla_detalles', 'regla_id')) {
                Schema::table('verificacion_regla_detalles', function (Blueprint $t) {
                    $t->index('regla_id');
                });
            }

            foreach ($this->fkConstraintsOnReglaId('verificacion_regla_detalles') as $fk) {
                try { DB::statement("ALTER TABLE `verificacion_regla_detalles` DROP FOREIGN KEY `{$fk}`"); } catch (\Throwable $e) {}
            }
            try {
                DB::statement(
                    "ALTER TABLE `verificacion_regla_detalles`
                     ADD CONSTRAINT `vrd_regla_fk`
                     FOREIGN KEY (`regla_id`) REFERENCES `verificacion_reglas`(`id`) ON DELETE CASCADE"
                );
            } catch (\Throwable $e) {}
        }

        // 3) UNIQUE y FK en calendario_verificacion
        if (Schema::hasTable('calendario_verificacion')) {
            // UNIQUE por clave lógica si no existe
            $uniqCols = ['estado','terminacion','mes_inicio','mes_fin','anio'];
            if (!$this->uniqueExistsByColumns('calendario_verificacion', $uniqCols)) {
                Schema::table('calendario_verificacion', function (Blueprint $t) {
                    $t->unique(['estado','terminacion','mes_inicio','mes_fin','anio'], 'cal_verif_regla_unica');
                });
            }

            if (Schema::hasColumn('calendario_verificacion','regla_id') &&
                Schema::hasTable('verificacion_reglas')) {

                // Índice en regla_id sólo si NO existe (aquí fallaba antes)
                if (!$this->columnHasIndex('calendario_verificacion', 'regla_id')) {
                    Schema::table('calendario_verificacion', function (Blueprint $t) {
                        $t->index('regla_id'); // Laravel lo nombra calendario_verificacion_regla_id_index
                    });
                }

                foreach ($this->fkConstraintsOnReglaId('calendario_verificacion') as $fk) {
                    try { DB::statement("ALTER TABLE `calendario_verificacion` DROP FOREIGN KEY `{$fk}`"); } catch (\Throwable $e) {}
                }
                try {
                    DB::statement(
                        "ALTER TABLE `calendario_verificacion`
                         ADD CONSTRAINT `cv_regla_fk`
                         FOREIGN KEY (`regla_id`) REFERENCES `verificacion_reglas`(`id`) ON DELETE CASCADE"
                    );
                } catch (\Throwable $e) {}
            }
        }
    }

    public function down(): void
    {
        // Quita UNIQUEs creados por esta migración si existen
        if (Schema::hasTable('verificacion_regla_estados')) {
            try { Schema::table('verificacion_regla_estados', function (Blueprint $t) { $t->dropUnique('vre_anio_estado_uniq'); }); } catch (\Throwable $e) {}
            // Cambia FK a “sin cascade” si la pusimos nosotros
            $fks = $this->fkConstraintsOnReglaId('verificacion_regla_estados');
            if (in_array('vre_regla_fk', $fks, true)) {
                try { DB::statement("ALTER TABLE `verificacion_regla_estados` DROP FOREIGN KEY `vre_regla_fk`"); } catch (\Throwable $e) {}
                try {
                    DB::statement(
                        "ALTER TABLE `verificacion_regla_estados`
                         ADD CONSTRAINT `vre_regla_fk_nocas`
                         FOREIGN KEY (`regla_id`) REFERENCES `verificacion_reglas`(`id`)"
                    );
                } catch (\Throwable $e) {}
            }
        }

        if (Schema::hasTable('calendario_verificacion')) {
            try { Schema::table('calendario_verificacion', function (Blueprint $t) { $t->dropUnique('cal_verif_regla_unica'); }); } catch (\Throwable $e) {}
            $fks = $this->fkConstraintsOnReglaId('calendario_verificacion');
            if (in_array('cv_regla_fk', $fks, true)) {
                try { DB::statement("ALTER TABLE `calendario_verificacion` DROP FOREIGN KEY `cv_regla_fk`"); } catch (\Throwable $e) {}
                try {
                    DB::statement(
                        "ALTER TABLE `calendario_verificacion`
                         ADD CONSTRAINT `cv_regla_fk_nocas`
                         FOREIGN KEY (`regla_id`) REFERENCES `verificacion_reglas`(`id`)"
                    );
                } catch (\Throwable $e) {}
            }
        }

        if (Schema::hasTable('verificacion_regla_detalles')) {
            $fks = $this->fkConstraintsOnReglaId('verificacion_regla_detalles');
            if (in_array('vrd_regla_fk', $fks, true)) {
                try { DB::statement("ALTER TABLE `verificacion_regla_detalles` DROP FOREIGN KEY `vrd_regla_fk`"); } catch (\Throwable $e) {}
                try {
                    DB::statement(
                        "ALTER TABLE `verificacion_regla_detalles`
                         ADD CONSTRAINT `vrd_regla_fk_nocas`
                         FOREIGN KEY (`regla_id`) REFERENCES `verificacion_reglas`(`id`)"
                    );
                } catch (\Throwable $e) {}
            }
        }
    }
};
