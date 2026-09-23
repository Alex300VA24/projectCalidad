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
            ConvenioMovilidadSeeder::class,
        ]);

        $path = database_path('data/silabos-visados.json');

        if (! is_file($path)) {
            return;
        }

        $semestres = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($semestres as $semestre) {
            $periodo = PeriodoAcademico::query()->firstOrCreate(['codigo' => $semestre['semestre']], ['activo' => true]);
            [$year, $term] = explode('-', $semestre['semestre']);
            $publicationDate = sprintf('%s-%s-01', $year, $term === 'I' ? '03' : '08');

            foreach ($semestre['ciclos'] as $ciclo) {
                $cicloAcademico = [
                    'I' => 1,
                    'II' => 2,
                    'III' => 3,
                    'IV' => 4,
                    'V' => 5,
                    'VI' => 6,
                    'VII' => 7,
                    'VIII' => 8,
                    'IX' => 9,
                    'X' => 10,
                ][$ciclo['ciclo']] ?? null;

                foreach ($ciclo['silabos'] as $silabo) {
                    $nombre = trim($silabo['curso'] ?? '');
                    $enlace = trim($silabo['link'] ?? '');

                    if ($nombre === '' || $enlace === '' || $cicloAcademico === null) {
                        continue;
                    }

                    Document::updateOrCreate(
                        [
                            'title' => $nombre,
                            'document_type' => Document::TIPO_SILABO,
                            'periodo_academico_id' => $periodo->id,
                        ],
                        [
                            'ciclo_academico' => $cicloAcademico,
                            'drive_url' => $enlace,
                            'publication_date' => $publicationDate,
                        ],
                    );
                }
            }
        }

        $indicadoresPath = database_path('data/indicadores.json');

        if (! is_file($indicadoresPath)) {
            return;
        }

        $indicadoresPorProceso = json_decode(file_get_contents($indicadoresPath), true, 512, JSON_THROW_ON_ERROR);

        foreach ($indicadoresPorProceso as $proceso) {
            foreach ($proceso['indicadores'] ?? [] as $indicador) {
                $titulo = trim($indicador['titulo'] ?? '');
                $enlace = trim($indicador['link'] ?? '');

                if ($titulo === '' || $enlace === '') {
                    continue;
                }

                Document::updateOrCreate(
                    ['title' => $titulo, 'document_type' => Document::TIPO_CALIDAD],
                    [
                        'section' => $proceso['proceso'],
                        'drive_url' => $enlace,
                        'publication_date' => '2026-03-01',
                    ],
                );
            }
        }
    }
}
