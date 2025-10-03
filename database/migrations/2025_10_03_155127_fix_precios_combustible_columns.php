<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Si la tabla no existe, créala con el esquema final
        if (!Schema::hasTable('precios_combustible')) {
            Schema::create('precios_combustible', function (Blueprint $table) {
                $table->id();
                $table->string('combustible', 20);
                $table->decimal('precio_por_litro', 8, 3)->default(0);
                $table->timestamps();

                $table->unique('combustible', 'precios_combustible_combustible_unique');
            });
            return;
        }

        // 1) Agregar 'combustible' si falta (temporalmente NULL para poder poblarla)
        if (!Schema::hasColumn('precios_combustible', 'combustible')) {
            Schema::table('precios_combustible', function (Blueprint $table) {
                $table->string('combustible', 20)->nullable()->after('id');
            });
        }

        // 2) Copiar desde 'tipo_combustible' si existe
        if (Schema::hasColumn('precios_combustible', 'tipo_combustible')) {
            DB::statement("
                UPDATE precios_combustible
                SET combustible = tipo_combustible
                WHERE (combustible IS NULL OR combustible = '')
            ");
        }

        // 3) Asegurar 'precio_por_litro'
        if (!Schema::hasColumn('precios_combustible', 'precio_por_litro')) {
            Schema::table('precios_combustible', function (Blueprint $table) {
                $table->decimal('precio_por_litro', 8, 3)->default(0);
            });
        }

        // 4) NOT NULL en 'combustible'
        DB::statement("
            UPDATE precios_combustible
            SET combustible = 'Magna'
            WHERE combustible IS NULL OR combustible = ''
        ");
        DB::statement("ALTER TABLE precios_combustible MODIFY combustible VARCHAR(20) NOT NULL");

        // 5) UNIQUE en 'combustible' SOLO si no existe ya
        $uniqueExists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'precios_combustible')
            ->where('index_name', 'precios_combustible_combustible_unique')
            ->exists();

        if (!$uniqueExists) {
            // Verifica que no haya duplicados antes de crear el índice (ajústalo si aplica a tu data)
            // Opcional: resolver duplicados aquí si fuese necesario.
            DB::statement("CREATE UNIQUE INDEX precios_combustible_combustible_unique ON precios_combustible (combustible)");
        }

        // 6) Quitar columna vieja si existe (esto elimina cualquier índice atado a esa columna)
        if (Schema::hasColumn('precios_combustible', 'tipo_combustible')) {
            Schema::table('precios_combustible', function (Blueprint $table) {
                $table->dropColumn('tipo_combustible');
            });
        }
    }

    public function down(): void
    {
        // Reversión mínima: quitar UNIQUE y/o columna 'combustible' si quieres.
        // (Generalmente no es necesario revertir esta normalización.)
        // Aquí lo dejamos vacío para evitar perder datos.
    }
};
