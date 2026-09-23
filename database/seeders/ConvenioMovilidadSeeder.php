<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class ConvenioMovilidadSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/convenio-movilidad.json');

        if (! File::exists($path)) {
            return;
        }

        $categorias = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($categorias as $categoria => $documentos) {
            if ($this->esGrupoPorAnio($documentos)) {
                foreach ($documentos as $anio => $documentosDelAnio) {
                    $this->guardarDocumentos($categoria, $documentosDelAnio, (int) $anio);
                }

                continue;
            }

            $this->guardarDocumentos($categoria, $documentos);
        }
    }

    /**
     * @param  array<int, mixed>  $documentos
     */
    private function guardarDocumentos(string $categoria, array $documentos, ?int $anio = null): void
    {
        foreach ($documentos as $documento) {
            $titulo = trim((string) ($documento['nombre_documento'] ?? ''));
            $enlace = trim((string) ($documento['link'] ?? ''));

            if ($titulo === '' || $enlace === '') {
                continue;
            }

            $anioPublicacion = $anio ?? $this->anioDelTitulo($titulo) ?? now()->year;

            Document::updateOrCreate(
                [
                    'title' => $titulo,
                    'document_type' => Document::TIPO_INSTITUCIONAL,
                ],
                [
                    'section' => $categoria,
                    'description' => 'Documento institucional de movilidad y convenios.',
                    'drive_url' => $enlace,
                    'publication_date' => Carbon::create($anioPublicacion, 12, 31),
                ],
            );
        }
    }

    private function esGrupoPorAnio(mixed $documentos): bool
    {
        return is_array($documentos)
            && $documentos !== []
            && array_is_list($documentos) === false;
    }

    private function anioDelTitulo(string $titulo): ?int
    {
        return preg_match('/(?:^|[- ])(20\d{2})(?:[- .]|$)/', $titulo, $coincidencias) === 1
            ? (int) $coincidencias[1]
            : null;
    }
}
