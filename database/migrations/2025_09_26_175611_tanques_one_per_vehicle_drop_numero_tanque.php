<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 0) Deduplicar por seguridad (conserva la fila más reciente por vehiculo)
        DB::statement("
            DELETE t1 FROM tanques t1
            JOIN tanques t2
              ON t1.vehiculo_id = t2.vehiculo_id
             AND t1.id < t2.id
        ");

        // 1) (Robusto) Soltar cualquier FK que apunte a vehiculo_id en tanques, si existiera
        $fkRows = DB::select("
            SELECT rc.CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
            JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
              ON rc.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
             AND rc.CONSTRAINT_NAME   = k.CONSTRAINT_NAME
            WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
              AND rc.TABLE_NAME = 'tanques'
              AND k.COLUMN_NAME = 'vehiculo_id'
        ");
        foreach ($fkRows as $fk) {
            DB::statement('ALTER TABLE `tanques` DROP FOREIGN KEY `'.$fk->CONSTRAINT_NAME.'`');
        }

        // 2) Limpiar índices antiguos (si existen)
        $idxRows = DB::select("
            SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'tanques'
        ");
        $idxNames = array_map(fn($o) => $o->INDEX_NAME, $idxRows);
        foreach (['tanques_vehiculo_id_numero_tanque_unique','tanques_vehiculo_id_idx_tmp'] as $name) {
            if (in_array($name, $idxNames)) {
                DB::statement('ALTER TABLE `tanques` DROP INDEX `'.$name.'`');
            }
        }

        // 3) Eliminar columna numero_tanque si existe
        if (Schema::hasColumn('tanques', 'numero_tanque')) {
            Schema::table('tanques', function (Blueprint $table) {
                $table->dropColumn('numero_tanque');
            });
        }

        // 4) Asegurar UNIQUE(vehiculo_id) (solo si no existe)
        $hasUnique = DB::selectOne("
            SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'tanques'
               AND INDEX_NAME   = 'tanques_vehiculo_id_unique'
        ");
        if (!$hasUnique || (int)$hasUnique->c === 0) {
            Schema::table('tanques', function (Blueprint $table) {
                $table->unique('vehiculo_id', 'tanques_vehiculo_id_unique');
            });
        }

        // 5) Recrear FK (apoyado ahora en vehiculo_id que ya tiene índice)
        Schema::table('tanques', function (Blueprint $table) {
            $table->foreign('vehiculo_id', 'tanques_vehiculo_id_foreign')
                  ->references('id')->on('vehiculos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // A) Soltar cualquier FK actual
        $fkRows = DB::select("
            SELECT rc.CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
            WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
              AND rc.TABLE_NAME = 'tanques'
        ");
        foreach ($fkRows as $fk) {
            DB::statement('ALTER TABLE `tanques` DROP FOREIGN KEY `'.$fk->CONSTRAINT_NAME.'`');
        }

        // B) Restaurar numero_tanque si no existe
        if (!Schema::hasColumn('tanques', 'numero_tanque')) {
            Schema::table('tanques', function (Blueprint $table) {
                $table->tinyInteger('numero_tanque')->unsigned()->nullable()->after('cantidad_tanques');
            });
        }

        // C) Restaurar índice compuesto (si no está)
        $idxRows = DB::select("
            SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'tanques'
        ");
        $idxNames = array_map(fn($o) => $o->INDEX_NAME, $idxRows);
        if (!in_array('tanques_vehiculo_id_numero_tanque_unique', $idxNames)) {
            DB::statement('ALTER TABLE `tanques` ADD UNIQUE KEY `tanques_vehiculo_id_numero_tanque_unique` (`vehiculo_id`,`numero_tanque`)');
        }

        // D) Quitar UNIQUE(vehiculo_id) si existe
        if (in_array('tanques_vehiculo_id_unique', $idxNames)) {
            DB::statement('ALTER TABLE `tanques` DROP INDEX `tanques_vehiculo_id_unique`');
        }

        // E) Recrear FK como estaba (no obligatorio si no lo tenías antes)
        Schema::table('tanques', function (Blueprint $table) {
            $table->foreign('vehiculo_id', 'tanques_vehiculo_id_foreign')
                  ->references('id')->on('vehiculos')
                  ->onDelete('cascade');
        });
    }
};
