<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\PeriodoAcademico;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ProcedureCatalogSeeder::class,
            IndicadoresSeeder::class,
        ]);

        $silabos = json_decode(file_get_contents(database_path('data/silabos.json')), true);

        foreach ($silabos as $silabo) {
            $periodo = PeriodoAcademico::query()->firstOrCreate(['codigo' => $silabo['seccion']], ['activo' => true]);

            Document::updateOrCreate(
                ['title' => $silabo['nombre'], 'document_type' => Document::TIPO_SILABO],
                [
                    'periodo_academico_id' => $periodo->id,
                    'ciclo_academico' => $silabo['ciclo'],
                    'drive_url' => $silabo['enlace'],
                    'publication_date' => $silabo['fecha'],
                ],
            );
        }
    }
}
