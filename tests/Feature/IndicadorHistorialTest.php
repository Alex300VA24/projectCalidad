<?php

namespace Tests\Feature;

use App\Livewire\Indicadores\IndicadorHistorial;
use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\ProgramaEstudio;
use App\Models\User;
use App\Services\CalculadorIndicadoresService;
use App\Services\IndicadorCompetenciasEsperadasService;
use App\Services\IndicadorDesaprobadosDosOMasVecesService;
use App\Services\IndicadorDesaprobadosPorCohorteService;
use App\Services\IndicadorDesaprobadosService;
use App\Services\IndicadorEgresadosService;
use App\Services\IndicadorTutoriaService;
use Database\Seeders\IndicadoresSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndicadorHistorialTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_visitor_registers_a_new_period_using_the_manual_formula(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $programa = ProgramaEstudio::query()->firstOrFail();
        $indicador = IndicadorMaestro::query()->where('codigo', CalculadorIndicadoresService::CODIGO_SILABOS)->firstOrFail();

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->call('editar', '2025-II')
            ->set('numerador', '18')
            ->set('denominador', '20')
            ->call('guardar')
            ->assertHasNoErrors();

        $medicion = IndicadorMedicion::query()
            ->where('indicador_id', $indicador->id)
            ->where('programa_estudio_id', $programa->id)
            ->where('periodo_academico', '2025-II')
            ->firstOrFail();

        $this->assertSame(90.0, (float) $medicion->valor_medido);
        $this->assertSame('CONFORME', $medicion->estado_cumplimiento);
        $this->assertSame(['origen' => 'manual', 'numerador' => 18, 'denominador' => 20], $medicion->datos_fuente);
    }

    public function test_consolidated_period_cannot_be_edited(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $user = User::factory()->create();
        $programa = ProgramaEstudio::query()->firstOrFail();
        $indicador = IndicadorMaestro::query()->where('codigo', CalculadorIndicadoresService::CODIGO_SILABOS)->firstOrFail();
        IndicadorMedicion::factory()->create([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2024-II',
            'valor_medido' => 90,
            'meta_programada' => 80,
            'estado_cumplimiento' => 'CONFORME',
            'consolidada_en' => now(),
        ]);

        Livewire::actingAs($user)->test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->call('editar', '2024-II')
            ->assertStatus(404);
    }

    public function test_historial_page_renders_the_indicator_formula_and_process(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()->where('codigo', CalculadorIndicadoresService::CODIGO_SILABOS)->firstOrFail();

        $this->get(route('quality-indicators.historial', $indicador->codigo))
            ->assertOk()
            ->assertSee($indicador->nombre)
            ->assertSee('N° de sílabos visados');
    }

    public function test_syllabus_chart_explains_latest_result_goal_and_trend(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $programa = ProgramaEstudio::query()->firstOrFail();
        $indicador = IndicadorMaestro::query()->where('codigo', CalculadorIndicadoresService::CODIGO_SILABOS)->firstOrFail();
        IndicadorMedicion::factory()->create([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2024-II',
            'valor_medido' => 70,
            'meta_programada' => 80,
            'estado_cumplimiento' => 'NO_CONFORME',
        ]);
        IndicadorMedicion::factory()->create([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2024-I',
            'valor_medido' => 85,
            'meta_programada' => 80,
            'estado_cumplimiento' => 'CONFORME',
        ]);

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->call('cambiarVista', 'grafico')
            ->assertSee('Interpretación del gráfico')
            ->assertSee('meta institucional')
            ->assertSee('puntos porcentuales')
            ->assertSee('respecto a 2026-I');
    }

    public function test_retention_table_and_chart_show_desertion_rate_and_interpretation(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $programa = ProgramaEstudio::query()->firstOrFail();
        $indicador = IndicadorMaestro::query()->where('codigo', CalculadorIndicadoresService::CODIGO_RETENCION)->firstOrFail();
        IndicadorMedicion::query()
            ->where('indicador_id', $indicador->id)
            ->where('programa_estudio_id', $programa->id)
            ->delete();
        IndicadorMedicion::query()->updateOrCreate([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2025-I',
        ], [
            'valor_medido' => 80,
            'meta_programada' => 90,
            'estado_cumplimiento' => 'OBSERVADO',
        ]);
        IndicadorMedicion::query()->updateOrCreate([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2025-II',
        ], [
            'valor_medido' => 92.5,
            'meta_programada' => 90,
            'estado_cumplimiento' => 'CONFORME',
        ]);

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSee('Tasa deserción')
            ->assertSee('20.0%')
            ->assertSee('7.5%')
            ->call('cambiarVista', 'grafico')
            ->assertSee('Interpretación del gráfico')
            ->assertSee('tasa de deserción de 7,5%')
            ->assertSee('supera la meta institucional');
    }

    public function test_desaprobados_indicator_uses_latest_json_period_and_updates_courses(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()->where('codigo', IndicadorDesaprobadosService::CODIGO)->firstOrFail();

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSet('periodoSeleccionado', '2026-I')
            ->assertSet('vista', 'tabla')
            ->assertSee('Histórico y registro')
            ->assertSee('Ver gráfico')
            ->assertSee('ALGORITMO Y PROGRAMACION')
            ->assertSee('36')
            ->assertDontSee('Porcentaje de estudiantes desaprobados por experiencia curricular en el período')
            ->call('cambiarVista', 'grafico')
            ->assertSet('vista', 'grafico')
            ->assertSee('Porcentaje de estudiantes desaprobados por experiencia curricular en el período')
            ->assertSee('Interpretación del gráfico')
            ->assertSee('Mayor porcentaje observado')
            ->assertSee('No representa un promedio general del período.')
            ->assertSee('Cargando período…')
            ->set('periodoSeleccionado', '2024-II')
            ->assertSee('ALGORITMOS Y COMPLEJIDAD')
            ->assertSee('6.35')
            ->call('cambiarVista', 'tabla')
            ->assertSee('6.35%');
    }

    public function test_desaprobados_indicator_route_accepts_its_official_code_with_a_slash(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()->where('codigo', IndicadorDesaprobadosService::CODIGO)->firstOrFail();

        $this->get(route('quality-indicators.historial-with-slash', $indicador->codigo))
            ->assertOk()
            ->assertSee('M01.03.02.02/PG-I1')
            ->assertSee('Porcentaje de desaprobados por experiencia curricular');
    }

    public function test_desaprobados_indicator_shows_a_clear_json_loading_error(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()->where('codigo', IndicadorDesaprobadosService::CODIGO)->firstOrFail();
        $this->app->instance(
            IndicadorDesaprobadosService::class,
            new IndicadorDesaprobadosService(database_path('data/indicador-desaprobados-inexistente.json')),
        );

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSee('No se pudieron cargar los datos')
            ->assertSee('No se pudo cargar la información del indicador.');
    }

    public function test_desaprobados_indicator_shows_the_empty_period_state(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()->where('codigo', IndicadorDesaprobadosService::CODIGO)->firstOrFail();
        $rutaTemporal = tempnam(storage_path('framework/testing'), 'desaprobados-');
        file_put_contents($rutaTemporal, json_encode([
            'indicador' => 'Estudiantes desaprobados en cada experiencia curricular (%)',
            'periodos' => [[
                'periodo_interno' => '2027-I',
                'experiencias_curriculares' => [],
            ]],
        ], JSON_THROW_ON_ERROR));
        $this->app->instance(IndicadorDesaprobadosService::class, new IndicadorDesaprobadosService($rutaTemporal));

        try {
            Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
                ->assertSet('periodoSeleccionado', '2027-I')
                ->assertSee('No existen experiencias curriculares registradas para este período.');
        } finally {
            unlink($rutaTemporal);
        }
    }

    public function test_repeated_failures_indicator_uses_latest_json_period_and_updates_all_results(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorDesaprobadosDosOMasVecesService::CODIGO)
            ->firstOrFail();

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSet('periodoSeleccionado', '2026-I')
            ->assertSee('Resultado del indicador')
            ->assertSee('23.91 %')
            ->assertSee('88 estudiantes de un total de 368 matriculados.')
            ->assertSee('Segunda vez')
            ->assertSee('Tercera vez')
            ->assertSee('Cuarta vez')
            ->assertSee('Las barras muestran cantidades absolutas, no porcentajes.')
            ->set('periodoSeleccionado', '2025-II')
            ->assertSee('35.12 %')
            ->assertSee('118 estudiantes de un total de 336 matriculados.')
            ->assertSee('96')
            ->assertSee('20')
            ->assertSee('2');
    }

    public function test_repeated_failures_indicator_shows_empty_and_missing_period_states(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorDesaprobadosDosOMasVecesService::CODIGO)
            ->firstOrFail();
        $rutaTemporal = tempnam(storage_path('framework/testing'), 'reincidencia-');
        file_put_contents($rutaTemporal, json_encode([
            'indicador' => ['codigo' => IndicadorDesaprobadosDosOMasVecesService::CODIGO],
            'periodos' => [],
        ], JSON_THROW_ON_ERROR));
        $this->app->instance(
            IndicadorDesaprobadosDosOMasVecesService::class,
            new IndicadorDesaprobadosDosOMasVecesService($rutaTemporal),
        );

        try {
            Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
                ->assertSee('No existen períodos registrados.');
        } finally {
            unlink($rutaTemporal);
        }

        $this->app->instance(
            IndicadorDesaprobadosDosOMasVecesService::class,
            new IndicadorDesaprobadosDosOMasVecesService,
        );

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->set('periodoSeleccionado', '2030-I')
            ->assertSee('No existen datos para este período.');
    }

    public function test_repeated_failures_indicator_route_accepts_the_official_code_with_a_slash(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorDesaprobadosDosOMasVecesService::CODIGO)
            ->firstOrFail();

        $this->get(route('quality-indicators.historial-with-slash', $indicador->codigo))
            ->assertOk()
            ->assertSee('M01.03.02.02/PG-I2')
            ->assertSee('Resultado general por semestre');
    }

    public function test_repeated_failures_indicator_shows_a_clear_json_loading_error(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorDesaprobadosDosOMasVecesService::CODIGO)
            ->firstOrFail();
        $this->app->instance(
            IndicadorDesaprobadosDosOMasVecesService::class,
            new IndicadorDesaprobadosDosOMasVecesService(database_path('data/indicador-reincidencia-inexistente.json')),
        );

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSee('No se pudieron cargar los datos')
            ->assertSee('No se pudo cargar la información del indicador.');
    }

    public function test_cohort_failures_indicator_opens_latest_period_chart_and_filters_courses(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorDesaprobadosPorCohorteService::CODIGO)
            ->firstOrFail();

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSet('periodoSeleccionado', '2026-I')
            ->assertSet('vista', 'grafico')
            ->assertSee('M01.04/PG-I5')
            ->assertSee('Seguimiento al Desempeño de los Estudiantes')
            ->assertSee('Período académico')
            ->assertSeeInOrder(['36', 'experiencias curriculares analizadas'])
            ->assertSee('INTRODUCCION AL ANALISIS MATEMATICO')
            ->assertSee('16.09')
            ->assertSee('Desaprobaciones del período')
            ->assertSee('220 desaprobaciones registradas de 2028 matrículas en cursos.')
            ->assertSee('10.85 %')
            ->assertSee('sin desglose por promoción o cohorte')
            ->assertSee('Cargando período…')
            ->call('cambiarVista', 'tabla')
            ->assertSee('EXAMEN DE SUFICIENCIA')
            ->assertSee('Sin datos')
            ->assertSee('0%')
            ->call('cambiarVista', 'grafico')
            ->set('periodoSeleccionado', '2025-II')
            ->assertSeeInOrder(['38', 'experiencias curriculares analizadas'])
            ->assertSee('237 desaprobaciones registradas de 1779 matrículas en cursos.')
            ->assertSee('13.32 %')
            ->set('periodoSeleccionado', '2024-II')
            ->assertSee('ESTRUCTURA DE DATOS')
            ->assertDontSee('EXAMEN DE SUFICIENCIA');
    }

    public function test_cohort_failures_indicator_route_accepts_the_official_code_with_a_slash(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorDesaprobadosPorCohorteService::CODIGO)
            ->firstOrFail();

        $this->get(route('quality-indicators.historial-with-slash', $indicador->codigo))
            ->assertOk()
            ->assertSee('M01.04/PG-I5')
            ->assertSee('Porcentaje de desaprobados por período académico y experiencia curricular');
    }

    public function test_cohort_failures_indicator_shows_empty_period_and_json_error_states(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorDesaprobadosPorCohorteService::CODIGO)
            ->firstOrFail();
        $rutaTemporal = tempnam(storage_path('framework/testing'), 'cohorte-');
        file_put_contents($rutaTemporal, json_encode([
            'indicador' => ['codigo' => IndicadorDesaprobadosPorCohorteService::CODIGO],
            'periodos' => [[
                'periodo_interno' => '2027-I',
                'experiencias_curriculares' => [],
            ]],
        ], JSON_THROW_ON_ERROR));
        $this->app->instance(
            IndicadorDesaprobadosPorCohorteService::class,
            new IndicadorDesaprobadosPorCohorteService($rutaTemporal),
        );

        try {
            Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
                ->assertSet('periodoSeleccionado', '2027-I')
                ->assertSee('No existen experiencias curriculares registradas para este período.');
        } finally {
            unlink($rutaTemporal);
        }

        $rutaSinPeriodos = tempnam(storage_path('framework/testing'), 'cohorte-');
        file_put_contents($rutaSinPeriodos, json_encode([
            'indicador' => ['codigo' => IndicadorDesaprobadosPorCohorteService::CODIGO],
            'periodos' => [],
        ], JSON_THROW_ON_ERROR));
        $this->app->instance(
            IndicadorDesaprobadosPorCohorteService::class,
            new IndicadorDesaprobadosPorCohorteService($rutaSinPeriodos),
        );

        try {
            Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
                ->assertSet('periodoSeleccionado', '')
                ->assertSee('No existen períodos académicos registrados para este indicador.');
        } finally {
            unlink($rutaSinPeriodos);
        }

        $this->app->instance(
            IndicadorDesaprobadosPorCohorteService::class,
            new IndicadorDesaprobadosPorCohorteService(database_path('data/indicador-cohorte-inexistente.json')),
        );

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSee('No se pudieron cargar los datos')
            ->assertSee('No se pudo cargar la información del indicador.');
    }

    public function test_general_competencies_indicator_opens_latest_period_and_recalculates_the_aggregate(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorCompetenciasEsperadasService::CODIGO_GENERALES)
            ->firstOrFail();

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSet('periodoSeleccionado', '2026-I')
            ->assertSet('vista', 'grafico')
            ->assertSee('M01.04/PG-I2')
            ->assertSeeInOrder(['6', 'cursos de Estudios Generales analizados'])
            ->assertSee('Resultado agregado de las experiencias curriculares EG del período')
            ->assertSee('89.45 %')
            ->assertSee('407 logros registrados de 455 matrículas')
            ->assertSee('FISICA GENERAL')
            ->assertSee('75.29')
            ->assertSee('ni representa necesariamente estudiantes únicos')
            ->call('cambiarVista', 'tabla')
            ->assertSee('Código reporte')
            ->assertSee('13027')
            ->set('periodoSeleccionado', '2024-II')
            ->assertSee('ANALISIS MATEMATICO')
            ->assertDontSee('DESARROLLO PERSONAL');
    }

    public function test_specific_competencies_indicator_uses_generated_json_and_filters_periods(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorCompetenciasEsperadasService::CODIGO_ESPECIFICAS)
            ->firstOrFail();

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSet('periodoSeleccionado', '2026-I')
            ->assertSet('vista', 'grafico')
            ->assertSee('M01.04/PG-I3')
            ->assertSeeInOrder(['4', 'cursos de especialidad analizados'])
            ->assertSee('74.75 %')
            ->assertSee('225 logros registrados de 301 matrículas')
            ->assertSee('80.49')
            ->call('cambiarVista', 'tabla')
            ->assertSee('GEOMETRÍA ANALÍTICA')
            ->assertSee('No disponible')
            ->set('periodoSeleccionado', '2025-I')
            ->assertSee('82.49 %')
            ->assertSee('212 logros registrados de 257 matrículas');
    }

    public function test_competency_indicator_routes_accept_the_official_codes_with_slashes(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);

        foreach ([
            IndicadorCompetenciasEsperadasService::CODIGO_GENERALES,
            IndicadorCompetenciasEsperadasService::CODIGO_ESPECIFICAS,
        ] as $codigo) {
            $this->get(route('quality-indicators.historial-with-slash', $codigo))
                ->assertOk()
                ->assertSee($codigo)
                ->assertSee('Resultado agregado del período');
        }
    }

    public function test_general_competencies_indicator_shows_empty_and_json_error_states(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorCompetenciasEsperadasService::CODIGO_GENERALES)
            ->firstOrFail();
        $rutaTemporal = tempnam(storage_path('framework/testing'), 'competencias-');
        file_put_contents($rutaTemporal, json_encode([
            'indicador' => ['codigo' => IndicadorCompetenciasEsperadasService::CODIGO_GENERALES],
            'periodos' => [[
                'periodo_interno' => '2027-I',
                'experiencias_curriculares' => [],
            ]],
        ], JSON_THROW_ON_ERROR));
        $this->app->instance(
            IndicadorCompetenciasEsperadasService::class,
            new IndicadorCompetenciasEsperadasService($rutaTemporal),
        );

        try {
            Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
                ->assertSet('periodoSeleccionado', '2027-I')
                ->assertSee('No existen experiencias curriculares de Estudios Generales registradas para este período.');
        } finally {
            unlink($rutaTemporal);
        }

        $this->app->instance(
            IndicadorCompetenciasEsperadasService::class,
            new IndicadorCompetenciasEsperadasService(database_path('data/competencias-inexistentes.json')),
        );

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSee('No se pudieron cargar los datos')
            ->assertSee('No se pudo cargar la información del indicador.');
    }

    public function test_graduate_promotion_comparison_allows_period_changes_only_in_the_data_view(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorEgresadosService::CODIGO_EGRESADOS_PROMOCION)
            ->firstOrFail();

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSet('periodoSeleccionado', '2026')
            ->assertSet('vista', 'grafico')
            ->assertSee('52.63 %')
            ->assertSee('40 de 76 registros considerados')
            ->assertDontSeeHtml('<select wire:model.live="periodoSeleccionado"')
            ->call('cambiarVista', 'tabla')
            ->assertSeeHtml('<select wire:model.live="periodoSeleccionado"')
            ->set('periodoSeleccionado', '2022')
            ->assertSee('57.97 %')
            ->assertSee('Valores usados en la fórmula')
            ->assertSee('Total de ingresantes')
            ->assertSee('egresados_por_promocion.json')
            ->call('cambiarVista', 'grafico')
            ->assertDontSeeHtml('<select wire:model.live="periodoSeleccionado"');
    }

    public function test_graduate_indicator_routes_accept_all_official_codes_with_slashes(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);

        foreach (IndicadorEgresadosService::codigos() as $codigo) {
            $this->get(route('quality-indicators.historial-with-slash', $codigo))
                ->assertOk()
                ->assertSee($codigo)
                ->assertSee('Resultado por cohorte, promoción o período');
        }
    }

    public function test_retired_indicator_history_is_not_available(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::factory()->create([
            'codigo' => 'M01.05-DCU-FI-001',
            'nombre' => 'Egresados titulados',
            'macro_proceso' => 'Resultados de la Formación',
        ]);

        $this->get(route('quality-indicators.historial-with-slash', $indicador->codigo))
            ->assertNotFound();
    }

    public function test_employer_satisfaction_indicator_remains_available(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorEgresadosService::CODIGO_SATISFACCION_EMPLEADORES)
            ->firstOrFail();

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSet('periodoSeleccionado', '2026')
            ->assertSee('83.00 %')
            ->assertSee('Satisfacción de los empleadores (%)')
            ->assertSeeHtml('<select wire:model.live="periodoSeleccionado"')
            ->call('cambiarVista', 'tabla')
            ->assertSee('Desarrollo de Software y Programación')
            ->assertSee('satisfaccion_empleadores.json');
    }

    public function test_graduate_indicator_shows_empty_and_json_error_states(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorEgresadosService::CODIGO_EMPLEABILIDAD)
            ->firstOrFail();
        $rutaTemporal = tempnam(storage_path('framework/testing'), 'egresados-');
        file_put_contents($rutaTemporal, json_encode(['periodos' => []], JSON_THROW_ON_ERROR));
        $this->app->instance(IndicadorEgresadosService::class, new IndicadorEgresadosService([
            IndicadorEgresadosService::CODIGO_EMPLEABILIDAD => $rutaTemporal,
        ]));

        try {
            Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
                ->assertSet('periodoSeleccionado', '')
                ->assertSee('Sin períodos registrados');
        } finally {
            unlink($rutaTemporal);
        }

        $this->app->instance(IndicadorEgresadosService::class, new IndicadorEgresadosService([
            IndicadorEgresadosService::CODIGO_EMPLEABILIDAD => database_path('data/empleabilidad-inexistente.json'),
        ]));

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSee('No se pudieron cargar los datos')
            ->assertSee('No se pudo cargar la información del indicador.');
    }

    public function test_tutoring_objectives_indicator_selects_latest_period_and_preserves_status(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorTutoriaService::CODIGO_LOGRO_OBJETIVOS)
            ->firstOrFail();

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSet('periodoSeleccionado', '2026-I')
            ->assertSet('vista', 'grafico')
            ->assertSee('100.00 %')
            ->assertSee('4 de 4 registros considerados')
            ->assertSee('Estado: verde')
            ->assertSee('Comparativo por período')
            ->assertSee('Gráfico de barras comparativo por período.')
            ->assertSee('Todos los períodos registrados')
            ->assertDontSeeHtml('<select wire:model.live="periodoSeleccionado"')
            ->call('cambiarVista', 'tabla')
            ->assertSeeHtml('<select wire:model.live="periodoSeleccionado"')
            ->set('periodoSeleccionado', '2025-II')
            ->assertSee('80.00 %')
            ->assertSee('Estado: amarillo')
            ->assertSee('Actividades planificadas')
            ->assertSee('logro_objetivos_programa_tutoria.json')
            ->call('cambiarVista', 'grafico')
            ->assertDontSeeHtml('<select wire:model.live="periodoSeleccionado"');
    }

    public function test_student_tutoring_satisfaction_adds_both_satisfaction_levels(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorTutoriaService::CODIGO_SATISFACCION_ESTUDIANTE)
            ->firstOrFail();

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSet('periodoSeleccionado', '2026-I')
            ->assertSee('94.06 %')
            ->assertSee('2011 de 2138 registros considerados')
            ->assertSee('Comparativo por período')
            ->assertDontSeeHtml('<select wire:model.live="periodoSeleccionado"')
            ->call('cambiarVista', 'tabla')
            ->assertSeeHtml('<select wire:model.live="periodoSeleccionado"')
            ->set('periodoSeleccionado', '2025')
            ->assertSee('93.52 %')
            ->assertSee('satisfaccion_estudiante_consejeria_tutoria.json');
    }

    public function test_tutoring_indicator_routes_accept_the_official_codes_with_slashes(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);

        foreach (IndicadorTutoriaService::codigos() as $codigo) {
            $this->get(route('quality-indicators.historial-with-slash', $codigo))
                ->assertOk()
                ->assertSee($codigo)
                ->assertSee('Resultado por período de medición');
        }
    }

    public function test_tutoring_comparative_chart_preserves_the_json_error_state(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $indicador = IndicadorMaestro::query()
            ->where('codigo', IndicadorTutoriaService::CODIGO_LOGRO_OBJETIVOS)
            ->firstOrFail();
        $this->app->instance(IndicadorTutoriaService::class, new IndicadorTutoriaService([
            IndicadorTutoriaService::CODIGO_LOGRO_OBJETIVOS => database_path('data/tutoria-inexistente.json'),
        ]));

        Livewire::test(IndicadorHistorial::class, ['indicador' => $indicador])
            ->assertSee('No se pudieron cargar los datos')
            ->assertSee('No se pudo cargar la información del indicador.');
    }
}
