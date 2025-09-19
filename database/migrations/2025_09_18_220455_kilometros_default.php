<?php
// database/migrations/xxxx_xx_xx_xxxxxx_update_kilometraje_default.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->bigInteger('kilometros')
                  ->default(0)
                  ->nullable()
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->bigInteger('kilometros')
                  ->nullable()
                  ->default(null)
                  ->change();
        });
    }
};
