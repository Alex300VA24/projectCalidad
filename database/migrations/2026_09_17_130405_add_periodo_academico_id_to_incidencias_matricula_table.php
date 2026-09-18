<?php

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
        Schema::table('incidencias_matricula', function (Blueprint $table) {
            $table->foreignId('periodo_academico_id')->nullable()->after('programa_estudio_id')->constrained('periodos_academicos')->restrictOnDelete();
        });

        DB::table('periodos_academicos')->orderBy('id')->get(['id', 'codigo'])->each(function (object $periodo): void {
            DB::table('incidencias_matricula')->where('periodo_academico', $periodo->codigo)->update(['periodo_academico_id' => $periodo->id]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidencias_matricula', function (Blueprint $table) {
            $table->dropForeign(['periodo_academico_id']);
            $table->dropColumn('periodo_academico_id');
        });
    }
};
