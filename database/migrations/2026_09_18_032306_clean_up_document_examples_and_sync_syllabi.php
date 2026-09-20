<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('documents') || ! Schema::hasTable('periodos_academicos')) {
            return;
        }

        $path = database_path('data/silabos.json');

        if (! is_file($path)) {
            return;
        }

        $silabos = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($silabos): void {
            DB::table('documents')->whereIn('drive_url', [
                'https://drive.google.com/file/d/1EjemploPoliticaCalidad2026/view',
                'https://drive.google.com/file/d/1EjemploManualIndicadores26/view',
                'https://drive.google.com/file/d/1EjemploInformeTrimestre3/view',
                'https://drive.google.com/file/d/1EjemploPlanMejoraInstitucional/view',
            ])->delete();

            foreach ($silabos as $silabo) {
                $periodoId = DB::table('periodos_academicos')
                    ->where('codigo', $silabo['seccion'])
                    ->value('id');

                if ($periodoId === null) {
                    $periodoId = DB::table('periodos_academicos')->insertGetId([
                        'codigo' => $silabo['seccion'],
                        'activo' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('documents')
                    ->where('title', $silabo['nombre'])
                    ->where('drive_url', $silabo['enlace'])
                    ->update([
                        'document_type' => 'silabo',
                        'section' => null,
                        'periodo_academico_id' => $periodoId,
                        'publication_date' => $silabo['fecha'],
                        'updated_at' => now(),
                    ]);
            }
        });
    }

    /** This deterministic data cleanup cannot be safely reversed. */
    public function down(): void {}
};
