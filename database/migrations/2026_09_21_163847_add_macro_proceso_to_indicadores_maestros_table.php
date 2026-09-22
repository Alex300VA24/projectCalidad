<?php

use App\Services\CalculadorIndicadoresService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('indicadores_maestros', function (Blueprint $table) {
            $table->string('macro_proceso', 60)->nullable()->after('proceso');
        });

        DB::table('indicadores_maestros')
            ->where('codigo', CalculadorIndicadoresService::CODIGO_SILABOS)
            ->update(['macro_proceso' => 'Gestión Curricular']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indicadores_maestros', function (Blueprint $table) {
            $table->dropColumn('macro_proceso');
        });
    }
};
