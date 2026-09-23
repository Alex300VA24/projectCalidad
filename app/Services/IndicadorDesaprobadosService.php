<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

final class IndicadorDesaprobadosService
{
    public const CODIGO = 'M01.03.02.02/PG-I1';

    private string $rutaDatos;

    public function __construct(?string $rutaDatos = null)
    {
        $this->rutaDatos = $rutaDatos ?? database_path('data/estudiantes_desaprobados_por_experiencia_curricular.json');
    }

    /** @return list<string> */
    public function periodos(): array
    {
        $periodos = array_values(array_unique(array_map(
            fn (array $periodo): string => (string) ($periodo['periodo_interno'] ?? ''),
            $this->datos()['periodos'],
        )));

        $periodos = array_values(array_filter($periodos, fn (string $periodo): bool => $periodo !== ''));
        usort($periodos, fn (string $primerPeriodo, string $segundoPeriodo): int => $this->compararPeriodos($segundoPeriodo, $primerPeriodo));

        return $periodos;
    }

    /**
     * @return list<array{
     *     nombre_curso: string,
     *     codigo_curso: string,
     *     numero_desaprobados: int,
     *     total_matriculados: int,
     *     porcentaje_desaprobados: float
     * }>
     */
    public function experienciasDelPeriodo(string $periodoSeleccionado): array
    {
        foreach ($this->datos()['periodos'] as $periodo) {
            if (($periodo['periodo_interno'] ?? null) !== $periodoSeleccionado) {
                continue;
            }

            $experiencias = $periodo['experiencias_curriculares'] ?? [];

            if (! is_array($experiencias)) {
                throw new UnexpectedValueException('Las experiencias curriculares del indicador no tienen un formato válido.');
            }

            return array_map(function (mixed $experiencia): array {
                if (! is_array($experiencia)
                    || ! isset($experiencia['nombre_curso'], $experiencia['codigo_curso'], $experiencia['numero_desaprobados'], $experiencia['total_matriculados'])
                    || ! is_numeric($experiencia['numero_desaprobados'])
                    || ! is_numeric($experiencia['total_matriculados'])) {
                    throw new UnexpectedValueException('Una experiencia curricular del indicador no tiene un formato válido.');
                }

                $numeroDesaprobados = (int) $experiencia['numero_desaprobados'];
                $totalMatriculados = (int) $experiencia['total_matriculados'];

                return [
                    'nombre_curso' => (string) $experiencia['nombre_curso'],
                    'codigo_curso' => (string) $experiencia['codigo_curso'],
                    'numero_desaprobados' => $numeroDesaprobados,
                    'total_matriculados' => $totalMatriculados,
                    'porcentaje_desaprobados' => $this->calcularPorcentajeDesaprobados($numeroDesaprobados, $totalMatriculados),
                ];
            }, $experiencias);
        }

        return [];
    }

    public function calcularPorcentajeDesaprobados(int $numeroDesaprobados, int $totalMatriculados): float
    {
        if ($totalMatriculados <= 0) {
            return 0.0;
        }

        return round(($numeroDesaprobados / $totalMatriculados) * 100, 2);
    }

    /** @return array{indicador: string, periodos: list<array<string, mixed>>} */
    private function datos(): array
    {
        if (! File::isFile($this->rutaDatos)) {
            throw new RuntimeException('No se encontró el archivo de datos del indicador.');
        }

        try {
            $datos = json_decode(File::get($this->rutaDatos), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('El archivo de datos del indicador no contiene JSON válido.', previous: $exception);
        }

        if (! is_array($datos) || ! isset($datos['indicador'], $datos['periodos']) || ! is_array($datos['periodos'])) {
            throw new UnexpectedValueException('El archivo de datos del indicador no tiene la estructura esperada.');
        }

        return $datos;
    }

    private function compararPeriodos(string $primerPeriodo, string $segundoPeriodo): int
    {
        return $this->valorPeriodo($primerPeriodo) <=> $this->valorPeriodo($segundoPeriodo)
            ?: strcmp($primerPeriodo, $segundoPeriodo);
    }

    private function valorPeriodo(string $periodo): int
    {
        if (preg_match('/^(\d{4})-(I|II)$/', $periodo, $coincidencias) !== 1) {
            return 0;
        }

        return ((int) $coincidencias[1] * 10) + ($coincidencias[2] === 'II' ? 2 : 1);
    }
}
