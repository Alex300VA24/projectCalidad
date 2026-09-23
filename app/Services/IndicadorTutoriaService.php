<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use UnexpectedValueException;

class IndicadorTutoriaService
{
    public const CODIGO_LOGRO_OBJETIVOS = 'M01.04/PG-I7';

    public const CODIGO_SATISFACCION_ESTUDIANTE = 'M01.04/PG-I8';

    /** @param  array<string, string>  $rutasFuentes */
    public function __construct(private readonly array $rutasFuentes = []) {}

    /** @return list<string> */
    public static function codigos(): array
    {
        return array_keys(self::definiciones());
    }

    /** @return list<string> */
    public function periodos(string $codigo): array
    {
        $definicion = $this->definicion($codigo);
        $registros = $this->registros($codigo, $definicion['archivo']);
        $periodos = [];

        foreach ($registros as $registro) {
            if (! is_array($registro)) {
                continue;
            }

            $periodo = trim((string) ($registro[$definicion['campo_periodo']] ?? ''));

            if ($periodo !== '') {
                $periodos[] = $periodo;
            }
        }

        $periodos = array_values(array_unique($periodos));
        usort($periodos, fn (string $periodoA, string $periodoB): int => strnatcasecmp($periodoB, $periodoA));

        return $periodos;
    }

    /**
     * @return array{
     *     periodo: string,
     *     etiqueta_periodo: string,
     *     numerador: float|int,
     *     denominador: float|int,
     *     porcentaje: float|null,
     *     etiqueta_numerador: string,
     *     etiqueta_denominador: string,
     *     fuente: string,
     *     estado: string|null,
     *     desglose: array{}
     * }|null
     */
    public function resultado(string $codigo, string $periodo): ?array
    {
        $definicion = $this->definicion($codigo);

        foreach ($this->registros($codigo, $definicion['archivo']) as $registro) {
            if (! is_array($registro) || (string) ($registro[$definicion['campo_periodo']] ?? '') !== $periodo) {
                continue;
            }

            $numerador = $this->sumarCampos($registro, $definicion['campos_numerador']);
            $denominador = Arr::get($registro, $definicion['campo_denominador']);

            if (! is_numeric($denominador)) {
                throw new UnexpectedValueException('El período seleccionado no contiene un denominador numérico válido.');
            }

            $denominador = $this->normalizarNumero($denominador);

            if ($numerador < 0 || $denominador < 0 || ($denominador > 0 && $numerador > $denominador)) {
                throw new UnexpectedValueException('Las métricas del período seleccionado están fuera del rango permitido.');
            }

            $estado = trim(mb_strtolower((string) ($registro['estado'] ?? '')));

            return [
                'periodo' => $periodo,
                'etiqueta_periodo' => $definicion['etiqueta_periodo'],
                'numerador' => $numerador,
                'denominador' => $denominador,
                'porcentaje' => $this->calcularPorcentaje($numerador, $denominador),
                'etiqueta_numerador' => $definicion['etiqueta_numerador'],
                'etiqueta_denominador' => $definicion['etiqueta_denominador'],
                'fuente' => $definicion['archivo'],
                'estado' => $estado !== '' ? $estado : null,
                'desglose' => [],
            ];
        }

        return null;
    }

    public function calcularPorcentaje(float|int $numerador, float|int $denominador): ?float
    {
        if ($denominador <= 0) {
            return null;
        }

        return round(($numerador / $denominador) * 100, 2);
    }

    /**
     * @return array{
     *     archivo: string,
     *     campo_periodo: string,
     *     etiqueta_periodo: string,
     *     campos_numerador: list<string>,
     *     campo_denominador: string,
     *     etiqueta_numerador: string,
     *     etiqueta_denominador: string
     * }
     */
    private function definicion(string $codigo): array
    {
        $definicion = self::definiciones()[$codigo] ?? null;

        if ($definicion === null) {
            throw new UnexpectedValueException("El indicador {$codigo} no está configurado.");
        }

        return $definicion;
    }

    /** @return array<string, array<string, mixed>> */
    private static function definiciones(): array
    {
        return [
            self::CODIGO_LOGRO_OBJETIVOS => [
                'archivo' => 'logro_objetivos_programa_tutoria.json',
                'campo_periodo' => 'periodo',
                'etiqueta_periodo' => 'Período académico',
                'campos_numerador' => ['metricas.actividades_ejecutadas'],
                'campo_denominador' => 'metricas.actividades_planificadas',
                'etiqueta_numerador' => 'Actividades ejecutadas',
                'etiqueta_denominador' => 'Actividades planificadas',
            ],
            self::CODIGO_SATISFACCION_ESTUDIANTE => [
                'archivo' => 'satisfaccion_estudiante_consejeria_tutoria.json',
                'campo_periodo' => 'periodo',
                'etiqueta_periodo' => 'Período de encuesta',
                'campos_numerador' => [
                    'metricas.numero_estudiantes_satisfechos',
                    'metricas.numero_estudiantes_muy_satisfechos',
                ],
                'campo_denominador' => 'metricas.numero_total_estudiantes',
                'etiqueta_numerador' => 'Estudiantes satisfechos o muy satisfechos',
                'etiqueta_denominador' => 'Total de estudiantes encuestados',
            ],
        ];
    }

    /** @return list<mixed> */
    private function registros(string $codigo, string $archivo): array
    {
        $ruta = $this->rutasFuentes[$codigo] ?? database_path('data/'.$archivo);

        if (! File::isFile($ruta) || ! File::isReadable($ruta)) {
            throw new UnexpectedValueException("No se puede leer la fuente {$archivo}.");
        }

        $datos = json_decode(File::get($ruta), true, 512, JSON_THROW_ON_ERROR);
        $registros = is_array($datos) ? ($datos['periodos'] ?? null) : null;

        if (! is_array($registros)) {
            throw new UnexpectedValueException('La fuente del indicador no contiene una lista de períodos válida.');
        }

        return $registros;
    }

    /** @param  list<string>  $campos */
    private function sumarCampos(array $registro, array $campos): float|int
    {
        $total = 0.0;

        foreach ($campos as $campo) {
            $valor = Arr::get($registro, $campo);

            if (! is_numeric($valor)) {
                throw new UnexpectedValueException('El período seleccionado no contiene un numerador numérico válido.');
            }

            $total += (float) $valor;
        }

        return $this->normalizarNumero($total);
    }

    private function normalizarNumero(mixed $valor): float|int
    {
        $numero = (float) $valor;

        return floor($numero) === $numero ? (int) $numero : $numero;
    }
}
