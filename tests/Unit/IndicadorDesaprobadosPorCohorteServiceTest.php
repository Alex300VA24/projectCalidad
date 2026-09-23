<?php

namespace Tests\Unit;

use App\Services\IndicadorDesaprobadosPorCohorteService;
use Tests\TestCase;
use UnexpectedValueException;

class IndicadorDesaprobadosPorCohorteServiceTest extends TestCase
{
    public function test_returns_periods_in_real_reverse_chronological_order(): void
    {
        $servicio = new IndicadorDesaprobadosPorCohorteService;

        $this->assertSame(['2026-I', '2025-II', '2025-I', '2024-II'], $servicio->periodos());
    }

    public function test_calculates_each_course_percentage_and_preserves_source_order(): void
    {
        $servicio = new IndicadorDesaprobadosPorCohorteService;

        $experiencias = $servicio->experienciasDelPeriodo('2026-I');
        $introduccionAnalisis = collect($experiencias)->firstWhere('codigo_curso', '1863');

        $this->assertCount(36, $experiencias);
        $this->assertSame('ALGORITMO Y PROGRAMACION', $experiencias[0]['nombre_curso']);
        $this->assertSame(73, $introduccionAnalisis['numero_aprobados']);
        $this->assertSame(14, $introduccionAnalisis['numero_desaprobados']);
        $this->assertSame(87, $introduccionAnalisis['total_matriculados']);
        $this->assertSame(16.09, $introduccionAnalisis['porcentaje_desaprobados']);
        $this->assertSame('2026-I', $introduccionAnalisis['periodo']);
    }

    public function test_uses_the_corrected_course_counts_for_every_period(): void
    {
        $servicio = new IndicadorDesaprobadosPorCohorteService;

        $this->assertCount(34, $servicio->experienciasDelPeriodo('2024-II'));
        $this->assertCount(39, $servicio->experienciasDelPeriodo('2025-I'));
        $this->assertCount(38, $servicio->experienciasDelPeriodo('2025-II'));
        $this->assertCount(36, $servicio->experienciasDelPeriodo('2026-I'));
    }

    public function test_calculates_the_weighted_period_result_from_course_enrollments(): void
    {
        $servicio = new IndicadorDesaprobadosPorCohorteService;

        $resultado = $servicio->calcularResultadoAgregado(
            $servicio->experienciasDelPeriodo('2025-II'),
        );

        $this->assertSame(237, $resultado['total_desaprobaciones']);
        $this->assertSame(1779, $resultado['total_matriculas']);
        $this->assertSame(13.32, $resultado['porcentaje']);
    }

    public function test_distinguishes_zero_percent_from_a_course_without_enrollment_data(): void
    {
        $servicio = new IndicadorDesaprobadosPorCohorteService;

        $experiencias = $servicio->experienciasDelPeriodo('2026-I');
        $algoritmo = collect($experiencias)->firstWhere('codigo_curso', '1945');
        $examenSuficiencia = collect($experiencias)->firstWhere('codigo_curso', '5213');

        $this->assertSame(0.0, $algoritmo['porcentaje_desaprobados']);
        $this->assertNull($examenSuficiencia['porcentaje_desaprobados']);
        $this->assertNull($servicio->calcularPorcentajeDesaprobados(0, 0));
    }

    public function test_returns_an_empty_list_for_a_period_without_registered_courses(): void
    {
        $servicio = new IndicadorDesaprobadosPorCohorteService;

        $this->assertSame([], $servicio->experienciasDelPeriodo('2030-I'));
    }

    public function test_rejects_course_quantities_that_do_not_match_enrollment_total(): void
    {
        $rutaTemporal = tempnam(storage_path('framework/testing'), 'cohorte-');
        file_put_contents($rutaTemporal, json_encode([
            'indicador' => ['codigo' => IndicadorDesaprobadosPorCohorteService::CODIGO],
            'periodos' => [[
                'periodo_interno' => '2027-I',
                'experiencias_curriculares' => [[
                    'nombre_curso' => 'CURSO DE PRUEBA',
                    'codigo_curso' => 'TEST-1',
                    'numero_estudiantes_aprobados' => 8,
                    'numero_estudiantes_desaprobados' => 3,
                    'total_estudiantes_matriculados' => 10,
                ]],
            ]],
        ], JSON_THROW_ON_ERROR));

        try {
            $this->expectException(UnexpectedValueException::class);

            (new IndicadorDesaprobadosPorCohorteService($rutaTemporal))->experienciasDelPeriodo('2027-I');
        } finally {
            unlink($rutaTemporal);
        }
    }
}
