<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use UnexpectedValueException;

class IndicadorEgresadosService
{
    public const CODIGO_TITULADOS_COHORTE = 'M01.03.04/PG-I1';

    public const CODIGO_TITULADOS_DOCE_MESES = 'M01.03.04/PG-I2';

    public const CODIGO_EGRESADOS_PROMOCION = 'M01.05/PG-I1';

    public const CODIGO_EMPLEABILIDAD = 'M01.05/PG-I2';

    public const CODIGO_OBJETIVOS_EDUCACIONALES = 'M01.05/PG-I3';

    public const CODIGO_SATISFACCION_EGRESADOS = 'M01.05/PG-I4';

    public const CODIGO_SATISFACCION_EMPLEADORES = 'M01.05/PG-I5';

    /** @param  array<string, string>  $rutasFuentes */
    public function __construct(private readonly array $rutasFuentes = []) {}

    /** @return list<string> */
    public static function codigos(): array
    {
        return array_keys(self::definiciones());
    }

    public function soporta(string $codigo): bool
    {
        return array_key_exists($codigo, self::definiciones());
    }

    /** @return list<string> */
    public function periodos(string $codigo): array
    {
        $definicion = $this->definicion($codigo);
        $datos = $this->leerFuente($codigo, $definicion['archivo']);
        $registros = $datos['periodos'] ?? null;

        if (! is_array($registros)) {
            throw new UnexpectedValueException('La fuente del indicador no contiene una lista de períodos válida.');
        }

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
     *     desglose: list<array{dimension: string, porcentaje: float}>
     * }|null
     */
    public function resultado(string $codigo, string $periodo): ?array
    {
        $definicion = $this->definicion($codigo);
        $datos = $this->leerFuente($codigo, $definicion['archivo']);
        $registros = $datos['periodos'] ?? null;

        if (! is_array($registros)) {
            throw new UnexpectedValueException('La fuente del indicador no contiene una lista de períodos válida.');
        }

        foreach ($registros as $registro) {
            if (! is_array($registro) || (string) ($registro[$definicion['campo_periodo']] ?? '') !== $periodo) {
                continue;
            }

            $numerador = Arr::get($registro, $definicion['campo_numerador']);
            $denominador = Arr::get($registro, $definicion['campo_denominador']);

            if (! is_numeric($numerador) || ! is_numeric($denominador)) {
                throw new UnexpectedValueException('El período seleccionado no contiene métricas numéricas válidas.');
            }

            $numerador = $this->normalizarNumero($numerador);
            $denominador = $this->normalizarNumero($denominador);

            if ($numerador < 0 || $denominador < 0 || ($denominador > 0 && $numerador > $denominador)) {
                throw new UnexpectedValueException('Las métricas del período seleccionado están fuera del rango permitido.');
            }

            return [
                'periodo' => $periodo,
                'etiqueta_periodo' => $definicion['etiqueta_periodo'],
                'numerador' => $numerador,
                'denominador' => $denominador,
                'porcentaje' => $this->calcularPorcentaje($numerador, $denominador),
                'etiqueta_numerador' => $definicion['etiqueta_numerador'],
                'etiqueta_denominador' => $definicion['etiqueta_denominador'],
                'fuente' => $definicion['archivo'],
                'desglose' => $this->desglose($registro, $definicion['campo_desglose'] ?? null),
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
     *     campo_numerador: string,
     *     campo_denominador: string,
     *     etiqueta_numerador: string,
     *     etiqueta_denominador: string,
     *     campo_desglose?: string
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

    /** @return array<string, array<string, string>> */
    private static function definiciones(): array
    {
        return [
            self::CODIGO_TITULADOS_COHORTE => [
                'archivo' => 'egresados_titulados_por_cohorte.json',
                'campo_periodo' => 'periodo_cohorte',
                'etiqueta_periodo' => 'Cohorte',
                'campo_numerador' => 'metricas_calculo_promedio.numero_egresados_titulados_promedio',
                'campo_denominador' => 'metricas_calculo_promedio.numero_total_egresados_promedio',
                'etiqueta_numerador' => 'Egresados titulados',
                'etiqueta_denominador' => 'Total de egresados',
            ],
            self::CODIGO_TITULADOS_DOCE_MESES => [
                'archivo' => 'egresados_titulados_12_meses.json',
                'campo_periodo' => 'periodo_cohorte',
                'etiqueta_periodo' => 'Cohorte',
                'campo_numerador' => 'metricas_calculo_promedio.numero_titulados_12_meses_promedio',
                'campo_denominador' => 'metricas_calculo_promedio.numero_total_graduados_promedio',
                'etiqueta_numerador' => 'Titulados dentro de doce meses',
                'etiqueta_denominador' => 'Total de graduados',
            ],
            self::CODIGO_EGRESADOS_PROMOCION => [
                'archivo' => 'egresados_por_promocion.json',
                'campo_periodo' => 'periodo_promocion',
                'etiqueta_periodo' => 'Promoción',
                'campo_numerador' => 'metricas.numero_egresados_promedio',
                'campo_denominador' => 'metricas.numero_total_ingresantes',
                'etiqueta_numerador' => 'Egresados',
                'etiqueta_denominador' => 'Total de ingresantes',
            ],
            self::CODIGO_EMPLEABILIDAD => [
                'archivo' => 'empleabilidad_egresados.json',
                'campo_periodo' => 'periodo_encuesta',
                'etiqueta_periodo' => 'Período de encuesta',
                'campo_numerador' => 'metricas.numero_egresados_trabajando',
                'campo_denominador' => 'metricas.numero_total_encuestados',
                'etiqueta_numerador' => 'Egresados trabajando en su área',
                'etiqueta_denominador' => 'Total de egresados encuestados',
            ],
            self::CODIGO_OBJETIVOS_EDUCACIONALES => [
                'archivo' => 'cumplimiento_objetivos_educacionales.json',
                'campo_periodo' => 'periodo_encuesta',
                'etiqueta_periodo' => 'Período de evaluación',
                'campo_numerador' => 'metricas.numero_oe_bueno_muy_bueno',
                'campo_denominador' => 'metricas.numero_total_oe',
                'etiqueta_numerador' => 'Objetivos con nivel bueno o muy bueno',
                'etiqueta_denominador' => 'Total de objetivos educacionales',
            ],
            self::CODIGO_SATISFACCION_EGRESADOS => [
                'archivo' => 'satisfaccion_egresados.json',
                'campo_periodo' => 'periodo_encuesta',
                'etiqueta_periodo' => 'Período de encuesta',
                'campo_numerador' => 'metricas.numero_satisfechos_muy_satisfechos',
                'campo_denominador' => 'metricas.numero_total_encuestados',
                'etiqueta_numerador' => 'Egresados satisfechos o muy satisfechos',
                'etiqueta_denominador' => 'Total de egresados encuestados',
            ],
            self::CODIGO_SATISFACCION_EMPLEADORES => [
                'archivo' => 'satisfaccion_empleadores.json',
                'campo_periodo' => 'periodo_encuesta',
                'etiqueta_periodo' => 'Período de encuesta',
                'campo_numerador' => 'metricas.numero_empleadores_satisfechos',
                'campo_denominador' => 'metricas.numero_total_encuestados',
                'etiqueta_numerador' => 'Empleadores satisfechos o muy satisfechos',
                'etiqueta_denominador' => 'Total de empleadores encuestados',
                'campo_desglose' => 'metricas.competencias_especificas',
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function leerFuente(string $codigo, string $archivo): array
    {
        $ruta = $this->rutasFuentes[$codigo] ?? database_path('data/'.$archivo);

        if (! File::isFile($ruta) || ! File::isReadable($ruta)) {
            throw new UnexpectedValueException("No se puede leer la fuente {$archivo}.");
        }

        $datos = json_decode(File::get($ruta), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($datos)) {
            throw new UnexpectedValueException("La fuente {$archivo} no contiene un objeto JSON válido.");
        }

        return $datos;
    }

    /** @return list<array{dimension: string, porcentaje: float}> */
    private function desglose(array $registro, ?string $campo): array
    {
        if ($campo === null) {
            return [];
        }

        $items = Arr::get($registro, $campo, []);

        if (! is_array($items)) {
            throw new UnexpectedValueException('El desglose de competencias no es válido.');
        }

        $desglose = [];

        foreach ($items as $item) {
            $dimension = trim((string) ($item['dimension'] ?? ''));
            $porcentaje = $item['nivel_satisfaccion'] ?? null;

            if ($dimension === '' || ! is_numeric($porcentaje) || $porcentaje < 0 || $porcentaje > 100) {
                throw new UnexpectedValueException('El desglose de competencias contiene valores inválidos.');
            }

            $desglose[] = ['dimension' => $dimension, 'porcentaje' => round((float) $porcentaje, 2)];
        }

        return $desglose;
    }

    private function normalizarNumero(mixed $valor): float|int
    {
        $numero = (float) $valor;

        return floor($numero) === $numero ? (int) $numero : $numero;
    }
}
