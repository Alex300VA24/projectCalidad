<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Document;
use App\Models\PeriodoAcademico;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ImportSilabosTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_distinct_silabos_with_the_same_drive_url_are_imported(): void
    {
        $file = $this->createSilabosFile([
            [
                'nombre' => 'Técnicas Digitales para Computación',
                'seccion' => '2025-I',
                'ciclo' => 5,
                'enlace' => 'https://drive.google.com/drive/folders/carpeta-compartida',
                'fecha' => '2026-03-01',
            ],
            [
                'nombre' => 'Inteligencia Artificial I',
                'seccion' => '2025-I',
                'ciclo' => 5,
                'enlace' => 'https://drive.google.com/drive/folders/carpeta-compartida',
                'fecha' => '2026-03-01',
            ],
        ]);

        try {
            $this->artisan('app:import-silabos', ['--file' => $file])
                ->expectsOutput('Sílabos importados: 2 nuevos, 0 actualizados.')
                ->assertSuccessful();
        } finally {
            unlink($file);
        }

        $periodo = PeriodoAcademico::query()->where('codigo', '2025-I')->firstOrFail();

        $this->assertDatabaseHas('documents', [
            'title' => 'Técnicas Digitales para Computación',
            'document_type' => 'silabo',
            'periodo_academico_id' => $periodo->id,
            'ciclo_academico' => 5,
            'drive_url' => 'https://drive.google.com/drive/folders/carpeta-compartida',
        ]);
        $this->assertDatabaseHas('documents', [
            'title' => 'Inteligencia Artificial I',
            'document_type' => 'silabo',
            'periodo_academico_id' => $periodo->id,
            'ciclo_academico' => 5,
            'drive_url' => 'https://drive.google.com/drive/folders/carpeta-compartida',
        ]);
        $this->assertDatabaseCount('documents', 2);
    }

    public function test_existing_silabo_is_updated_without_being_duplicated(): void
    {
        $periodo = PeriodoAcademico::query()->firstOrCreate(['codigo' => '2025-I'], ['activo' => true]);

        Document::create([
            'title' => 'Inteligencia Artificial I',
            'document_type' => 'silabo',
            'periodo_academico_id' => $periodo->id,
            'ciclo_academico' => 5,
            'description' => null,
            'drive_url' => 'https://drive.google.com/drive/folders/enlace-anterior',
            'publication_date' => '2025-03-01',
        ]);

        $file = $this->createSilabosFile([
            [
                'nombre' => 'Inteligencia Artificial I',
                'seccion' => '2025-I',
                'ciclo' => 5,
                'enlace' => 'https://drive.google.com/drive/folders/enlace-nuevo',
                'fecha' => '2026-03-01',
            ],
        ]);

        try {
            $this->artisan('app:import-silabos', ['--file' => $file])
                ->expectsOutput('Sílabos importados: 0 nuevos, 1 actualizados.')
                ->assertSuccessful();
        } finally {
            unlink($file);
        }

        $this->assertDatabaseHas('documents', [
            'title' => 'Inteligencia Artificial I',
            'document_type' => 'silabo',
            'periodo_academico_id' => $periodo->id,
            'ciclo_academico' => 5,
            'drive_url' => 'https://drive.google.com/drive/folders/enlace-nuevo',
            'publication_date' => '2026-03-01 00:00:00',
        ]);
        $this->assertDatabaseCount('documents', 1);
    }

    /**
     * @param  list<array{nombre: string, seccion: string, ciclo: int, enlace: string, fecha: string}>  $silabos
     */
    private function createSilabosFile(array $silabos): string
    {
        $file = tempnam(sys_get_temp_dir(), 'silabos-');

        file_put_contents($file, json_encode($silabos, JSON_THROW_ON_ERROR));

        return $file;
    }
}
