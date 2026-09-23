<?php

namespace Tests\Unit;

use App\Services\IndicadorTutoriaService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use UnexpectedValueException;

class IndicadorTutoriaServiceTest extends TestCase
{
    /** @return array<string, array{string, string, int, int, float, string|null}> */
    public static function resultadosEsperados(): array
    {
        return [
            'logro de objetivos' => [IndicadorTutoriaService::CODIGO_LOGRO_OBJETIVOS, '2025-II', 4, 5, 80.0, 'amarillo'],
            'satisfacción estudiantil' => [IndicadorTutoriaService::CODIGO_SATISFACCION_ESTUDIANTE, '2026-I', 2011, 2138, 94.06, null],
        ];
    }

    #[Test]
    #[DataProvider('resultadosEsperados')]
    public function each_indicator_calculates_its_percentage_from_absolute_json_values(
        string $codigo,
        string $periodo,
        int $numerador,
        int $denominador,
        float $porcentaje,
        ?string $estado,
    ): void {
        $servicio = new IndicadorTutoriaService;

        $resultado = $servicio->resultado($codigo, $periodo);

        $this->assertNotNull($resultado);
        $this->assertSame($numerador, $resultado['numerador']);
        $this->assertSame($denominador, $resultado['denominador']);
        $this->assertSame($porcentaje, $resultado['porcentaje']);
        $this->assertSame($estado, $resultado['estado']);
    }

    #[Test]
    public function periods_are_unique_and_sorted_from_latest_to_oldest(): void
    {
        $servicio = new IndicadorTutoriaService;

        $periodos = $servicio->periodos(IndicadorTutoriaService::CODIGO_LOGRO_OBJETIVOS);

        $this->assertSame(['2026-I', '2025-II', '2025-I', '2024-II', '2024-I'], $periodos);
    }

    #[Test]
    public function zero_denominator_returns_no_percentage_without_dividing_by_zero(): void
    {
        $servicio = new IndicadorTutoriaService;

        $this->assertNull($servicio->calcularPorcentaje(0, 0));
        $this->assertSame(0.0, $servicio->calcularPorcentaje(0, 20));
    }

    #[Test]
    public function a_combined_numerator_greater_than_the_total_is_rejected(): void
    {
        $rutaTemporal = tempnam(storage_path('framework/testing'), 'tutoria-');
        file_put_contents($rutaTemporal, json_encode([
            'periodos' => [[
                'periodo' => '2027-I',
                'metricas' => [
                    'numero_estudiantes_satisfechos' => 12,
                    'numero_estudiantes_muy_satisfechos' => 9,
                    'numero_total_estudiantes' => 20,
                ],
            ]],
        ], JSON_THROW_ON_ERROR));
        $servicio = new IndicadorTutoriaService([
            IndicadorTutoriaService::CODIGO_SATISFACCION_ESTUDIANTE => $rutaTemporal,
        ]);

        try {
            $this->expectException(UnexpectedValueException::class);
            $servicio->resultado(IndicadorTutoriaService::CODIGO_SATISFACCION_ESTUDIANTE, '2027-I');
        } finally {
            unlink($rutaTemporal);
        }
    }
}
