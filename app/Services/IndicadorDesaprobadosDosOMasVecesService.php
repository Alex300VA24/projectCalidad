<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

final class IndicadorDesaprobadosDosOMasVecesService
{
    public const CODIGO = 'M01.03.02.02/PG-I2';

    private string $rutaDatos;

    public function __construct(?string $rutaDatos = null)
    {
        $this->rutaDatos = $rutaDatos ?? database_path('data/estudiantes_desaprobados_dos_o_mas_veces_general_por_semestre_actualizado.json');
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
     * @return array{
     *     periodo_interno: string,
     *     segunda_vez: int,
     *     tercera_vez: int,
     *     cuarta_vez: int,
     *     total_estudiantes_dos_o_mas_veces: int,
     *     total_estudiantes_matriculados_semestre: int,
     *     porcentaje_indicador: float
     * }|null
     */
    public function datosDelPeriodo(string $periodoSeleccionado): ?array
    {
        foreach ($this->datos()['periodos'] as $periodo) {
            if (($periodo['periodo_interno'] ?? null) !== $periodoSeleccionado) {
                continue;
            }

            return $this->normalizarPeriodo($periodo);
        }

        return null;
    }

    public function calcularPorcentajeIndicador(int $totalReincidentes, int $totalMatriculados): float
    {
        if ($totalMatriculados <= 0) {
            return 0.0;
        }

        return round(($totalReincidentes / $totalMatriculados) * 100, 2);
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
            || ! is_array($datos['periodos'])) {
            throw new UnexpectedValueException('El archivo de datos del indicador no tiene la estructura esperada.');
        }

        return $datos;
    }

    /**
     * @param  array<string, mixed>  $periodo
     * @return array{
     *     periodo_interno: string,
     *     segunda_vez: int,
     *     tercera_vez: int,
     *     cuarta_vez: int,
     *     total_estudiantes_dos_o_mas_veces: int,
     *     total_estudiantes_matriculados_semestre: int,
     *     porcentaje_indicador: float
     * }
     */
    private function normalizarPeriodo(array $periodo): array
    {
        $campos = [
            'periodo_interno',
            'segunda_vez',
            'tercera_vez',
            'cuarta_vez',
            'total_estudiantes_dos_o_mas_veces',
            'total_estudiantes_matriculados_semestre',
        ];

        foreach ($campos as $campo) {
            if (! array_key_exists($campo, $periodo)) {
                throw new UnexpectedValueException('Un período del indicador no tiene la estructura esperada.');
            }
        }

        foreach (array_slice($campos, 1) as $campoNumerico) {
            if (! is_numeric($periodo[$campoNumerico]) || (int) $periodo[$campoNumerico] < 0) {
                throw new UnexpectedValueException('Un período del indicador contiene cantidades no válidas.');
            }
        }

        $segundaVez = (int) $periodo['segunda_vez'];
        $terceraVez = (int) $periodo['tercera_vez'];
        $cuartaVez = (int) $periodo['cuarta_vez'];
        $totalReincidentes = (int) $periodo['total_estudiantes_dos_o_mas_veces'];
        $totalMatriculados = (int) $periodo['total_estudiantes_matriculados_semestre'];

        if ($segundaVez + $terceraVez + $cuartaVez !== $totalReincidentes) {
            throw new UnexpectedValueException('El total de estudiantes de dos o más veces no coincide con sus categorías.');
        }

        return [
            'periodo_interno' => (string) $periodo['periodo_interno'],
            'segunda_vez' => $segundaVez,
            'tercera_vez' => $terceraVez,
            'cuarta_vez' => $cuartaVez,
            'total_estudiantes_dos_o_mas_veces' => $totalReincidentes,
            'total_estudiantes_matriculados_semestre' => $totalMatriculados,
            'porcentaje_indicador' => $this->calcularPorcentajeIndicador($totalReincidentes, $totalMatriculados),
        ];
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
