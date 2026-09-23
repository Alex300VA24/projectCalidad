<?php

namespace Tests\Unit;

use App\Services\IndicadorCompetenciasEsperadasService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use UnexpectedValueException;

class IndicadorCompetenciasEsperadasServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function general_competency_periods_are_sorted_and_only_eg_courses_are_returned(): void
    {
        $servicio = new IndicadorCompetenciasEsperadasService;

        $periodos = $servicio->periodos(IndicadorCompetenciasEsperadasService::CODIGO_GENERALES);
        $experiencias = $servicio->experienciasDelPeriodo(
            IndicadorCompetenciasEsperadasService::CODIGO_GENERALES,
            '2026-I',
        );

        $this->assertSame(['2026-I', '2025-II', '2025-I', '2024-II'], $periodos);
        $this->assertCount(6, $experiencias);
        $this->assertSame(['EG'], array_values(array_unique(array_column($experiencias, 'tipo'))));
        $this->assertSame('DESARROLLO PERSONAL', $experiencias[0]['nombre_curso']);
        $this->assertSame(100.0, $experiencias[0]['porcentaje_logro']);
        $this->assertSame(75.29, $experiencias[4]['porcentaje_logro']);
    }

    #[Test]
    public function general_competency_aggregate_uses_totals_instead_of_the_simple_average(): void
    {
        $servicio = new IndicadorCompetenciasEsperadasService;
        $experiencias = $servicio->experienciasDelPeriodo(
            IndicadorCompetenciasEsperadasService::CODIGO_GENERALES,
            '2026-I',
        );

        $resultado = $servicio->calcularResultadoAgregado($experiencias);

        $this->assertSame(407, $resultado['total_logros']);
        $this->assertSame(455, $resultado['total_matriculados']);
        $this->assertSame(89.45, $resultado['porcentaje']);
    }

    #[Test]
    public function specific_competency_source_preserves_course_order_and_calculates_its_aggregate(): void
    {
        $servicio = new IndicadorCompetenciasEsperadasService;

        $periodos = $servicio->periodos(IndicadorCompetenciasEsperadasService::CODIGO_ESPECIFICAS);
        $experiencias = $servicio->experienciasDelPeriodo(
            IndicadorCompetenciasEsperadasService::CODIGO_ESPECIFICAS,
            '2026-I',
        );
        $resultado = $servicio->calcularResultadoAgregado($experiencias);

        $this->assertSame(['2026-I', '2025-I'], $periodos);
        $this->assertSame([
            'GEOMETRÍA ANALÍTICA',
            'MATEMÁTICA DISCRETA',
            'FÍSICA PARA CIENCIA DE LA COMPUTACIÓN',
            'ANÁLISIS NUMÉRICO',
        ], array_column($experiencias, 'nombre_curso'));
        $this->assertSame(80.49, $experiencias[0]['porcentaje_logro']);
        $this->assertSame(225, $resultado['total_logros']);
        $this->assertSame(301, $resultado['total_matriculados']);
        $this->assertSame(74.75, $resultado['porcentaje']);
    }

    #[Test]
    public function zero_enrollment_returns_no_data_and_zero_logros_remains_zero_percent(): void
    {
        $servicio = new IndicadorCompetenciasEsperadasService;

        $this->assertNull($servicio->calcularPorcentajeLogro(0, 0));
        $this->assertSame(0.0, $servicio->calcularPorcentajeLogro(0, 12));
        $this->assertSame([
            'total_logros' => 0,
            'total_matriculados' => 0,
            'porcentaje' => null,
        ], $servicio->calcularResultadoAgregado([]));
    }

    #[Test]
    public function courses_outside_the_expected_curriculum_type_are_excluded(): void
    {
        $rutaTemporal = tempnam(storage_path('framework/testing'), 'competencias-');
        file_put_contents($rutaTemporal, json_encode([
            'indicador' => ['codigo' => IndicadorCompetenciasEsperadasService::CODIGO_GENERALES],
            'periodos' => [[
                'periodo_interno' => '2026-I',
                'experiencias_curriculares' => [
                    [
                        'nombre_curso' => 'CURSO EG',
                        'tipo' => 'EG',
                        'numero_estudiantes_que_logran_nivel_esperado' => 8,
                        'total_estudiantes_matriculados' => 10,
                    ],
                    [
                        'nombre_curso' => 'CURSO DE ESPECIALIDAD',
                        'tipo' => 'EE',
                        'numero_estudiantes_que_logran_nivel_esperado' => 9,
                        'total_estudiantes_matriculados' => 10,
                    ],
                ],
            ]],
        ], JSON_THROW_ON_ERROR));
        $servicio = new IndicadorCompetenciasEsperadasService($rutaTemporal);

        try {
            $experiencias = $servicio->experienciasDelPeriodo(
                IndicadorCompetenciasEsperadasService::CODIGO_GENERALES,
                '2026-I',
            );

            $this->assertSame(['CURSO EG'], array_column($experiencias, 'nombre_curso'));
        } finally {
            unlink($rutaTemporal);
        }
    }

    #[Test]
    public function invalid_counts_are_rejected(): void
    {
        $rutaTemporal = tempnam(storage_path('framework/testing'), 'competencias-');
        file_put_contents($rutaTemporal, json_encode([
            'indicador' => ['codigo' => IndicadorCompetenciasEsperadasService::CODIGO_GENERALES],
            'periodos' => [[
                'periodo_interno' => '2026-I',
                'experiencias_curriculares' => [[
                    'nombre_curso' => 'CURSO INVÁLIDO',
                    'tipo' => 'EG',
                    'numero_estudiantes_que_logran_nivel_esperado' => 11,
                    'total_estudiantes_matriculados' => 10,
                ]],
            ]],
        ], JSON_THROW_ON_ERROR));
        $servicio = new IndicadorCompetenciasEsperadasService($rutaTemporal);

        try {
            $this->expectException(UnexpectedValueException::class);
            $servicio->experienciasDelPeriodo(
                IndicadorCompetenciasEsperadasService::CODIGO_GENERALES,
                '2026-I',
            );
        } finally {
            unlink($rutaTemporal);
        }
    }
}
