<?php

namespace Tests\Unit;

use App\Services\IndicadorDesaprobadosService;
use Tests\TestCase;

class IndicadorDesaprobadosServiceTest extends TestCase
{
    public function test_it_calculates_the_percentage_with_two_decimals_and_avoids_division_by_zero(): void
    {
        $servicio = new IndicadorDesaprobadosService;

        $this->assertSame(16.09, $servicio->calcularPorcentajeDesaprobados(14, 87));
        $this->assertSame(0.0, $servicio->calcularPorcentajeDesaprobados(4, 0));
    }

    public function test_it_gets_dynamic_periods_and_preserves_the_json_course_order(): void
    {
        $servicio = new IndicadorDesaprobadosService;

        $this->assertSame(['2026-I', '2025-II', '2025-I', '2024-II'], $servicio->periodos());

        $experiencias = $servicio->experienciasDelPeriodo('2026-I');

        $this->assertSame('ALGORITMO Y PROGRAMACION', $experiencias[0]['nombre_curso']);
        $this->assertSame('1945', $experiencias[0]['codigo_curso']);
        $this->assertSame(0.0, $experiencias[0]['porcentaje_desaprobados']);
        $this->assertSame(36, count($experiencias));
    }

    public function test_it_returns_an_empty_list_for_a_period_without_registered_courses(): void
    {
        $servicio = new IndicadorDesaprobadosService;

        $this->assertSame([], $servicio->experienciasDelPeriodo('2030-I'));
    }
}
