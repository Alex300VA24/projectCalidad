<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

final class IndicadorDesaprobadosPorCohorteService
{
    public const CODIGO = 'M01.04/PG-I5';

    private string $rutaDatos;

    public function __construct(?string $rutaDatos = null)
    {
        $this->rutaDatos = $rutaDatos ?? database_path('data/indicador_M01_04_PG_I5_datos_actualizados.json');
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
     *     periodo: string,
     *     numero_aprobados: int,
     *     numero_desaprobados: int,
     *     total_matriculados: int,
     *     porcentaje_desaprobados: float|null
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

            return array_values(array_map(function (mixed $experiencia) use ($periodoSeleccionado): array {
                if (! is_array($experiencia)
                    || ! isset(
                        $experiencia['nombre_curso'],
                        $experiencia['codigo_curso'],
                        $experiencia['numero_estudiantes_aprobados'],
                        $experiencia['numero_estudiantes_desaprobados'],
                        $experiencia['total_estudiantes_matriculados'],
                    )
                    || ! is_numeric($experiencia['numero_estudiantes_aprobados'])
                    || ! is_numeric($experiencia['numero_estudiantes_desaprobados'])
                    || ! is_numeric($experiencia['total_estudiantes_matriculados'])) {
                    throw new UnexpectedValueException('Una experiencia curricular del indicador no tiene un formato válido.');
                }

                $numeroAprobados = (int) $experiencia['numero_estudiantes_aprobados'];
                $numeroDesaprobados = (int) $experiencia['numero_estudiantes_desaprobados'];
                $totalMatriculados = (int) $experiencia['total_estudiantes_matriculados'];

                if ($numeroAprobados < 0 || $numeroDesaprobados < 0 || $totalMatriculados < 0) {
                    throw new UnexpectedValueException('Las cantidades de una experiencia curricular no pueden ser negativas.');
                }

                if ($numeroAprobados + $numeroDesaprobados !== $totalMatriculados) {
                    throw new UnexpectedValueException('Las cantidades de una experiencia curricular no coinciden con el total de matriculados.');
                }

                return [
                    'nombre_curso' => (string) $experiencia['nombre_curso'],
                    'codigo_curso' => (string) $experiencia['codigo_curso'],
                    'periodo' => $periodoSeleccionado,
                    'numero_aprobados' => $numeroAprobados,
                    'numero_desaprobados' => $numeroDesaprobados,
                    'total_matriculados' => $totalMatriculados,
                    'porcentaje_desaprobados' => $this->calcularPorcentajeDesaprobados($numeroDesaprobados, $totalMatriculados),
                ];
            }, $experiencias));
        }

        return [];
    }

    /**
     * @param  list<array{numero_desaprobados: int, total_matriculados: int}>  $experiencias
     * @return array{total_desaprobaciones: int, total_matriculas: int, porcentaje: float|null}
     */
    public function calcularResultadoAgregado(array $experiencias): array
    {
        $totalDesaprobaciones = array_sum(array_column($experiencias, 'numero_desaprobados'));
        $totalMatriculas = array_sum(array_column($experiencias, 'total_matriculados'));

        return [
            'total_desaprobaciones' => $totalDesaprobaciones,
            'total_matriculas' => $totalMatriculas,
            'porcentaje' => $this->calcularPorcentajeDesaprobados($totalDesaprobaciones, $totalMatriculas),
        ];
    }

    public function calcularPorcentajeDesaprobados(int $numeroDesaprobados, int $totalMatriculados): ?float
    {
        if ($totalMatriculados <= 0) {
            return null;
        }

        return round(($numeroDesaprobados / $totalMatriculados) * 100, 2);
    }

    /** @return array{indicador: array<string, mixed>, periodos: list<array<string, mixed>>} */
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

        if (! is_array($datos)
            || ! isset($datos['indicador'], $datos['periodos'])
            || ! is_array($datos['indicador'])
            || ! is_array($datos['periodos'])
            || ($datos['indicador']['codigo'] ?? null) !== self::CODIGO) {
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
