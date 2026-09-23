<?php

namespace Tests\Unit;

use App\Services\IndicadorEgresadosService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use UnexpectedValueException;

class IndicadorEgresadosServiceTest extends TestCase
{
    /**
     * @return array<string, array{string, string, float|int, float|int, float}>
     */
    public static function resultadosEsperados(): array
    {
        return [
            'titulados por cohorte' => [IndicadorEgresadosService::CODIGO_TITULADOS_COHORTE, '2026', 20, 40, 50.0],
            'titulados en doce meses' => [IndicadorEgresadosService::CODIGO_TITULADOS_DOCE_MESES, '2026', 20, 40, 50.0],
            'egresados por promoción' => [IndicadorEgresadosService::CODIGO_EGRESADOS_PROMOCION, '2026', 40, 76, 52.63],
            'empleabilidad' => [IndicadorEgresadosService::CODIGO_EMPLEABILIDAD, '2026', 17, 20, 85.0],
            'objetivos educacionales' => [IndicadorEgresadosService::CODIGO_OBJETIVOS_EDUCACIONALES, '2026', 3, 3, 100.0],
            'satisfacción de egresados' => [IndicadorEgresadosService::CODIGO_SATISFACCION_EGRESADOS, '2026', 16, 20, 80.0],
            'satisfacción de empleadores' => [IndicadorEgresadosService::CODIGO_SATISFACCION_EMPLEADORES, '2026', 83, 100, 83.0],
        ];
    }

    #[Test]
    #[DataProvider('resultadosEsperados')]
    public function each_indicator_calculates_its_percentage_from_absolute_json_values(
        string $codigo,
        string $periodo,
        float|int $numerador,
        float|int $denominador,
        float $porcentaje,
    ): void {
        $servicio = new IndicadorEgresadosService;

        $resultado = $servicio->resultado($codigo, $periodo);

        $this->assertNotNull($resultado);
        $this->assertSame($numerador, $resultado['numerador']);
        $this->assertSame($denominador, $resultado['denominador']);
        $this->assertSame($porcentaje, $resultado['porcentaje']);
    }

    #[Test]
    public function promotion_periods_are_unique_and_sorted_from_latest_to_oldest(): void
    {
        $servicio = new IndicadorEgresadosService;

        $periodos = $servicio->periodos(IndicadorEgresadosService::CODIGO_EGRESADOS_PROMOCION);

        $this->assertSame(['2026', '2025', '2024', '2023', '2022'], $periodos);
    }

    #[Test]
    public function employer_satisfaction_exposes_the_competency_breakdown(): void
    {
        $resultado = (new IndicadorEgresadosService)->resultado(
            IndicadorEgresadosService::CODIGO_SATISFACCION_EMPLEADORES,
            '2026',
        );

        $this->assertNotNull($resultado);
        $this->assertCount(5, $resultado['desglose']);
        $this->assertSame('Desarrollo de Software y Programación', $resultado['desglose'][0]['dimension']);
        $this->assertSame(88.5, $resultado['desglose'][0]['porcentaje']);
    }

    #[Test]
    public function zero_denominator_returns_no_percentage_without_dividing_by_zero(): void
    {
        $servicio = new IndicadorEgresadosService;

        $this->assertNull($servicio->calcularPorcentaje(0, 0));
        $this->assertSame(0.0, $servicio->calcularPorcentaje(0, 20));
    }

    #[Test]
    public function invalid_absolute_values_are_rejected(): void
    {
        $rutaTemporal = tempnam(storage_path('framework/testing'), 'egresados-');
        file_put_contents($rutaTemporal, json_encode([
            'periodos' => [[
                'periodo_encuesta' => '2027',
                'metricas' => [
                    'numero_egresados_trabajando' => 21,
                    'numero_total_encuestados' => 20,
                ],
            ]],
        ], JSON_THROW_ON_ERROR));
        $servicio = new IndicadorEgresadosService([
            IndicadorEgresadosService::CODIGO_EMPLEABILIDAD => $rutaTemporal,
        ]);

        try {
            $this->expectException(UnexpectedValueException::class);
            $servicio->resultado(IndicadorEgresadosService::CODIGO_EMPLEABILIDAD, '2027');
        } finally {
            unlink($rutaTemporal);
        }
    }
}
