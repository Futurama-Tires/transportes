<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) calendario_verificacion: cambiar FK regla_id -> CASCADE
        if (Schema::hasTable('calendario_verificacion')) {
            // Primero dropeamos la FK actual (era ON DELETE SET NULL)
            Schema::table('calendario_verificacion', function (Blueprint $table) {
                try {
                    $table->dropForeign(['regla_id']); // nombre implícito: calendario_verificacion_regla_id_foreign
                } catch (\Throwable $e) {
                    // ignorar si ya no existe
                }
            });

            // Luego la recreamos con CASCADE
            Schema::table('calendario_verificacion', function (Blueprint $table) {
                $table->foreign('regla_id')
                    ->references('id')->on('verificacion_reglas')
                    ->onDelete('cascade');
            });
        }

        // 2) (idempotente) asegurar CASCADE en detalles/estados (ya lo tienes así en tu dump)
        if (Schema::hasTable('verificacion_regla_detalles')) {
            Schema::table('verificacion_regla_detalles', function (Blueprint $table) {
                try { $table->dropForeign(['regla_id']); } catch (\Throwable $e) {}
            });
            Schema::table('verificacion_regla_detalles', function (Blueprint $table) {
                $table->foreign('regla_id')
                    ->references('id')->on('verificacion_reglas')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('verificacion_regla_estados')) {
            Schema::table('verificacion_regla_estados', function (Blueprint $table) {
                try { $table->dropForeign(['regla_id']); } catch (\Throwable $e) {}
            });
            Schema::table('verificacion_regla_estados', function (Blueprint $table) {
                $table->foreign('regla_id')
                    ->references('id')->on('verificacion_reglas')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Revertir a ON DELETE SET NULL en calendario_verificacion
        if (Schema::hasTable('calendario_verificacion')) {
            Schema::table('calendario_verificacion', function (Blueprint $table) {
                try { $table->dropForeign(['regla_id']); } catch (\Throwable $e) {}
            });

            Schema::table('calendario_verificacion', function (Blueprint $table) {
                $table->foreign('regla_id')
                    ->references('id')->on('verificacion_reglas')
                    ->nullOnDelete(); // vuelve a ON DELETE SET NULL
            });
        }

        // Dejar detalles/estados con CASCADE (ya era su estado original según tu BD)
        if (Schema::hasTable('verificacion_regla_detalles')) {
            Schema::table('verificacion_regla_detalles', function (Blueprint $table) {
                try { $table->dropForeign(['regla_id']); } catch (\Throwable $e) {}
            });
            Schema::table('verificacion_regla_detalles', function (Blueprint $table) {
                $table->foreign('regla_id')
                    ->references('id')->on('verificacion_reglas')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('verificacion_regla_estados')) {
            Schema::table('verificacion_regla_estados', function (Blueprint $table) {
                try { $table->dropForeign(['regla_id']); } catch (\Throwable $e) {}
            });
            Schema::table('verificacion_regla_estados', function (Blueprint $table) {
                $table->foreign('regla_id')
                    ->references('id')->on('verificacion_reglas')
                    ->onDelete('cascade');
            });
        }
    }
};
