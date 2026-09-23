<?php

namespace Tests\Unit;

use App\Services\IndicadorDesaprobadosDosOMasVecesService;
use Tests\TestCase;

class IndicadorDesaprobadosDosOMasVecesServiceTest extends TestCase
{
    public function test_returns_dynamic_periods_with_the_latest_period_first(): void
    {
        $servicio = new IndicadorDesaprobadosDosOMasVecesService(
            dirname(__DIR__, 2).'/database/data/estudiantes_desaprobados_dos_o_mas_veces_general_por_semestre_actualizado.json',
        );

        $this->assertSame(['2026-I', '2025-II', '2025-I', '2024-II'], $servicio->periodos());
    }

    public function test_returns_period_counts_and_calculated_percentage(): void
    {
        $servicio = new IndicadorDesaprobadosDosOMasVecesService(
            dirname(__DIR__, 2).'/database/data/estudiantes_desaprobados_dos_o_mas_veces_general_por_semestre_actualizado.json',
        );

        $datos = $servicio->datosDelPeriodo('2026-I');

        $this->assertSame([
            'periodo_interno' => '2026-I',
            'segunda_vez' => 68,
            'tercera_vez' => 19,
            'cuarta_vez' => 1,
            'total_estudiantes_dos_o_mas_veces' => 88,
            'total_estudiantes_matriculados_semestre' => 368,
            'porcentaje_indicador' => 23.91,
        ], $datos);
    }

    public function test_calculates_expected_percentages_and_avoids_division_by_zero(): void
    {
        $servicio = new IndicadorDesaprobadosDosOMasVecesService(
            dirname(__DIR__, 2).'/database/data/estudiantes_desaprobados_dos_o_mas_veces_general_por_semestre_actualizado.json',
        );

        $this->assertSame(31.55, $servicio->calcularPorcentajeIndicador(100, 317));
        $this->assertSame(22.87, $servicio->calcularPorcentajeIndicador(78, 341));
        $this->assertSame(35.12, $servicio->calcularPorcentajeIndicador(118, 336));
        $this->assertSame(23.91, $servicio->calcularPorcentajeIndicador(88, 368));
        $this->assertSame(0.0, $servicio->calcularPorcentajeIndicador(10, 0));
    }

    public function test_returns_null_for_an_unregistered_period(): void
    {
        $servicio = new IndicadorDesaprobadosDosOMasVecesService(
            dirname(__DIR__, 2).'/database/data/estudiantes_desaprobados_dos_o_mas_veces_general_por_semestre_actualizado.json',
        );

        $this->assertNull($servicio->datosDelPeriodo('2030-I'));
    }
}
