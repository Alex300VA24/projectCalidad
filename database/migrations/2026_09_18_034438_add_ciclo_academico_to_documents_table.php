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
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedTinyInteger('ciclo_academico')->nullable()->after('periodo_academico_id');
        });

        $silabos = json_decode(file_get_contents(database_path('data/silabos.json')), true, 512, JSON_THROW_ON_ERROR);

        foreach ($silabos as $silabo) {
            DB::table('documents')
                ->where('title', $silabo['nombre'])
                ->where('drive_url', $silabo['enlace'])
                ->update(['ciclo_academico' => $silabo['ciclo']]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('ciclo_academico');
        });
    }
};
