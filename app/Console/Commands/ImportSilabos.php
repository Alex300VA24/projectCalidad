<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\PeriodoAcademico;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('app:import-silabos {--file= : Ruta al JSON de sílabos (por defecto database/data/silabos.json)}')]
#[Description('Importa sílabos visados (nombre + enlace Drive) desde un archivo JSON hacia la tabla documents')]
class ImportSilabos extends Command
{
    private const DEFAULT_DATES = [
        '2025-II' => '2025-08-01',
        '2026-I' => '2026-03-01',
    ];

    public function handle(): int
    {
        $path = $this->option('file') ?: database_path('data/silabos.json');

        if (! is_file($path)) {
            $this->error("Archivo no encontrado: {$path}");

            return self::FAILURE;
        }

        $entries = json_decode(file_get_contents($path), true);

        if (! is_array($entries)) {
            $this->error('JSON inválido: se esperaba un arreglo de sílabos.');

            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;

        foreach ($entries as $index => $entry) {
            $nombre = trim($entry['nombre'] ?? '');
            $seccion = trim($entry['seccion'] ?? '');
            $enlace = trim($entry['enlace'] ?? '');
            $ciclo = filter_var($entry['ciclo'] ?? null, FILTER_VALIDATE_INT);

            if ($nombre === '' || $seccion === '' || $enlace === '' || $ciclo === false || $ciclo < 1 || $ciclo > 10) {
                $this->warn("Fila {$index}: falta nombre, seccion, ciclo válido o enlace. Se omite.");

                continue;
            }

            $fecha = $entry['fecha'] ?? self::DEFAULT_DATES[$seccion] ?? now()->toDateString();
            $periodo = PeriodoAcademico::query()->firstOrCreate(['codigo' => $seccion], [
                'fecha_inicio' => self::DEFAULT_DATES[$seccion] ?? null,
                'activo' => true,
            ]);

            $document = Document::updateOrCreate(
                [
                    'title' => $nombre,
                    'document_type' => Document::TIPO_SILABO,
                    'periodo_academico_id' => $periodo->id,
                ],
                [
                    'description' => $entry['descripcion'] ?? null,
                    'ciclo_academico' => $ciclo,
                    'drive_url' => $enlace,
                    'publication_date' => Carbon::parse($fecha),
                ]
            );

            $document->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->info("Sílabos importados: {$created} nuevos, {$updated} actualizados.");

        return self::SUCCESS;
    }
}
