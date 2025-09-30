<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- calendario_id: quitar FK/índice si existen y luego la columna ---
        if (Schema::hasColumn('verificaciones', 'calendario_id')) {
            // ¿Hay una FK real asociada a calendario_id?
            $fk = DB::selectOne("
                SELECT CONSTRAINT_NAME AS name
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'verificaciones'
                  AND COLUMN_NAME = 'calendario_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");
            if ($fk && isset($fk->name)) {
                DB::statement("ALTER TABLE `verificaciones` DROP FOREIGN KEY `{$fk->name}`");
            }

            // ¿Hay un índice (no primario) sobre calendario_id?
            $idx = DB::selectOne("
                SELECT DISTINCT INDEX_NAME AS name
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'verificaciones'
                  AND COLUMN_NAME = 'calendario_id'
                  AND INDEX_NAME <> 'PRIMARY'
                LIMIT 1
            ");
            if ($idx && isset($idx->name)) {
                DB::statement("ALTER TABLE `verificaciones` DROP INDEX `{$idx->name}`");
            }

            // Ahora sí, elimina la columna
            Schema::table('verificaciones', function (Blueprint $table) {
                $table->dropColumn('calendario_id');
            });
        }

        // --- Otras columnas no usadas (¡NO tocar 'comentarios'!) ---
        Schema::table('verificaciones', function (Blueprint $table) {
            if (Schema::hasColumn('verificaciones', 'fecha_real')) {
                $table->dropColumn('fecha_real');
            }
            if (Schema::hasColumn('verificaciones', 'holograma')) {
                $table->dropColumn('holograma');
            }
            if (Schema::hasColumn('verificaciones', 'comprobante_path')) {
                $table->dropColumn('comprobante_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('verificaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('verificaciones', 'fecha_real')) {
                $table->date('fecha_real')->nullable()->after('fecha_verificacion');
            }
            if (!Schema::hasColumn('verificaciones', 'holograma')) {
                $table->string('holograma', 50)->nullable()->after('fecha_real');
            }
            if (!Schema::hasColumn('verificaciones', 'comprobante_path')) {
                $table->string('comprobante_path')->nullable()->after('holograma');
            }
            if (!Schema::hasColumn('verificaciones', 'calendario_id')) {
                $table->unsignedBigInteger('calendario_id')->nullable()->index()->after('comprobante_path');
            }
        });

        // Si quieres restaurar también la FK, descomenta y ajusta la tabla referenciada:
        // DB::statement("ALTER TABLE `verificaciones`
        //     ADD CONSTRAINT `verificaciones_calendario_id_foreign`
        //     FOREIGN KEY (`calendario_id`) REFERENCES `calendarios`(`id`) ON DELETE SET NULL");
    }
};
