<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('operadores', function (Blueprint $table) {
            // Identidad
            if (!Schema::hasColumn('operadores', 'tipo_sangre')) {
                $table->string('tipo_sangre', 5)->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('operadores', 'estado_civil')) {
                $table->string('estado_civil', 20)->nullable()->after('tipo_sangre');
            }
            if (!Schema::hasColumn('operadores', 'curp')) {
                $table->string('curp', 18)->nullable()->after('estado_civil');
            }
            if (!Schema::hasColumn('operadores', 'rfc')) {
                $table->string('rfc', 13)->nullable()->after('curp');
            }

            // Contacto de emergencia
            if (!Schema::hasColumn('operadores', 'contacto_emergencia_parentesco')) {
                $table->string('contacto_emergencia_parentesco', 50)->nullable()->after('rfc');
            }
            if (!Schema::hasColumn('operadores', 'contacto_emergencia_ubicacion')) {
                $table->string('contacto_emergencia_ubicacion', 100)->nullable()->after('contacto_emergencia_parentesco');
            }
            if (!Schema::hasColumn('operadores', 'contacto_emergencia_nombre')) {
                $table->string('contacto_emergencia_nombre', 100)->nullable()->after('contacto_emergencia_ubicacion');
            }
            if (!Schema::hasColumn('operadores', 'contacto_emergencia_tel')) {
                $table->string('contacto_emergencia_tel', 30)->nullable()->after('contacto_emergencia_nombre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('operadores', function (Blueprint $table) {
            if (Schema::hasColumn('operadores', 'tipo_sangre')) $table->dropColumn('tipo_sangre');
            if (Schema::hasColumn('operadores', 'estado_civil')) $table->dropColumn('estado_civil');
            if (Schema::hasColumn('operadores', 'curp')) $table->dropColumn('curp');
            if (Schema::hasColumn('operadores', 'rfc')) $table->dropColumn('rfc');
            if (Schema::hasColumn('operadores', 'contacto_emergencia_parentesco')) $table->dropColumn('contacto_emergencia_parentesco');
            if (Schema::hasColumn('operadores', 'contacto_emergencia_ubicacion')) $table->dropColumn('contacto_emergencia_ubicacion');
            if (Schema::hasColumn('operadores', 'contacto_emergencia_nombre')) $table->dropColumn('contacto_emergencia_nombre');
            if (Schema::hasColumn('operadores', 'contacto_emergencia_tel')) $table->dropColumn('contacto_emergencia_tel');
        });
    }
};
