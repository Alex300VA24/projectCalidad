<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

final class IndicadorCompetenciasEsperadasService
{
    public const CODIGO_GENERALES = 'M01.04/PG-I2';

    public const CODIGO_ESPECIFICAS = 'M01.04/PG-I3';

    private string $rutaCompetenciasGenerales;

    private string $rutaCompetenciasEspecificas;

    public function __construct(
        ?string $rutaCompetenciasGenerales = null,
        ?string $rutaCompetenciasEspecificas = null,
    ) {
        $this->rutaCompetenciasGenerales = $rutaCompetenciasGenerales
            ?? database_path('data/competencias_generales_esperadas_PG_I2.json');
        $this->rutaCompetenciasEspecificas = $rutaCompetenciasEspecificas
            ?? database_path('data/competencias_especificas_esperadas_PG_I3.json');
    }

    /** @return list<string> */
    public function periodos(string $codigoIndicador): array
    {
        $periodos = array_values(array_unique(array_map(
            fn (array $periodo): string => (string) ($periodo['periodo_interno'] ?? ''),
            $this->datos($codigoIndicador)['periodos'],
        )));

        $periodos = array_values(array_filter($periodos, fn (string $periodo): bool => $periodo !== ''));
        usort($periodos, fn (string $primerPeriodo, string $segundoPeriodo): int => $this->compararPeriodos($segundoPeriodo, $primerPeriodo));

        return $periodos;
    }

    /**
     * @return list<array{
     *     nombre_curso: string,
     *     codigo_curso_reporte: string|null,
     *     codigo_curso_plan_estudios: string|null,
     *     ciclo_plan: string|null,
     *     tipo: string,
     *     numero_estudiantes_que_logran_nivel_esperado: int,
     *     total_estudiantes_matriculados: int,
     *     porcentaje_logro: float|null
     * }>
     */
    public function experienciasDelPeriodo(string $codigoIndicador, string $periodoSeleccionado): array
    {
        $tipoEsperado = $this->configuracion($codigoIndicador)['tipo'];

        foreach ($this->datos($codigoIndicador)['periodos'] as $periodo) {
            if (($periodo['periodo_interno'] ?? null) !== $periodoSeleccionado) {
                continue;
            }

            $experiencias = $periodo['experiencias_curriculares'] ?? [];

            if (! is_array($experiencias)) {
                throw new UnexpectedValueException('Las experiencias curriculares del indicador no tienen un formato válido.');
            }

            $experienciasDelTipo = array_filter(
                $experiencias,
                fn (mixed $experiencia): bool => is_array($experiencia) && ($experiencia['tipo'] ?? null) === $tipoEsperado,
            );

            return array_values(array_map(function (mixed $experiencia): array {
                if (! is_array($experiencia)
                    || ! isset($experiencia['nombre_curso'], $experiencia['numero_estudiantes_que_logran_nivel_esperado'], $experiencia['total_estudiantes_matriculados'])
                    || ! is_numeric($experiencia['numero_estudiantes_que_logran_nivel_esperado'])
                    || ! is_numeric($experiencia['total_estudiantes_matriculados'])) {
                    throw new UnexpectedValueException('Una experiencia curricular del indicador no tiene un formato válido.');
                }

                $numeroLogros = (int) $experiencia['numero_estudiantes_que_logran_nivel_esperado'];
                $totalMatriculados = (int) $experiencia['total_estudiantes_matriculados'];

                if ($numeroLogros < 0 || $totalMatriculados < 0 || $numeroLogros > $totalMatriculados) {
                    throw new UnexpectedValueException('Los valores de una experiencia curricular del indicador no son válidos.');
                }

                return [
                    'nombre_curso' => (string) $experiencia['nombre_curso'],
                    'codigo_curso_reporte' => $this->textoOpcional($experiencia['codigo_curso_reporte'] ?? null),
                    'codigo_curso_plan_estudios' => $this->textoOpcional($experiencia['codigo_curso_plan_estudios'] ?? null),
                    'ciclo_plan' => $this->textoOpcional($experiencia['ciclo_plan'] ?? null),
                    'tipo' => (string) $experiencia['tipo'],
                    'numero_estudiantes_que_logran_nivel_esperado' => $numeroLogros,
                    'total_estudiantes_matriculados' => $totalMatriculados,
                    'porcentaje_logro' => $this->calcularPorcentajeLogro($numeroLogros, $totalMatriculados),
                ];
            }, $experienciasDelTipo));
        }

        return [];
    }

    public function calcularPorcentajeLogro(int $numeroLogros, int $totalMatriculados): ?float
    {
        if ($totalMatriculados <= 0) {
            return null;
        }

        return round(($numeroLogros / $totalMatriculados) * 100, 2);
    }

    /**
     * @param  list<array{numero_estudiantes_que_logran_nivel_esperado: int, total_estudiantes_matriculados: int}>  $experiencias
     * @return array{total_logros: int, total_matriculados: int, porcentaje: float|null}
     */
    public function calcularResultadoAgregado(array $experiencias): array
    {
        $totalLogros = array_sum(array_column($experiencias, 'numero_estudiantes_que_logran_nivel_esperado'));
        $totalMatriculados = array_sum(array_column($experiencias, 'total_estudiantes_matriculados'));

        return [
            'total_logros' => $totalLogros,
            'total_matriculados' => $totalMatriculados,
            'porcentaje' => $this->calcularPorcentajeLogro($totalLogros, $totalMatriculados),
        ];
    }

    /** @return array{indicador: array<string, mixed>, periodos: list<array<string, mixed>>} */
    private function datos(string $codigoIndicador): array
    {
        $configuracion = $this->configuracion($codigoIndicador);

        if (! File::isFile($configuracion['ruta'])) {
            throw new RuntimeException('No se encontró el archivo de datos del indicador.');
        }

        try {
            $datos = json_decode(File::get($configuracion['ruta']), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('El archivo de datos del indicador no contiene JSON válido.', previous: $exception);
        }

        if (! is_array($datos)
            || ! isset($datos['indicador'], $datos['periodos'])
            || ! is_array($datos['indicador'])
            || ! is_array($datos['periodos'])
            || ($datos['indicador']['codigo'] ?? null) !== $codigoIndicador) {
            throw new UnexpectedValueException('El archivo de datos del indicador no tiene la estructura esperada.');
        }

        return $datos;
    }

    /** @return array{ruta: string, tipo: string} */
    private function configuracion(string $codigoIndicador): array
    {
        return match ($codigoIndicador) {
            self::CODIGO_GENERALES => ['ruta' => $this->rutaCompetenciasGenerales, 'tipo' => 'EG'],
            self::CODIGO_ESPECIFICAS => ['ruta' => $this->rutaCompetenciasEspecificas, 'tipo' => 'EE'],
            default => throw new UnexpectedValueException('El código del indicador de competencias no es válido.'),
        };
    }

    private function textoOpcional(mixed $valor): ?string
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        return (string) $valor;
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
