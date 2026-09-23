<?php

namespace Tests\Feature;

use App\Livewire\Indicadores\DashboardCalidad;
use App\Models\Document;
use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\ProgramaEstudio;
use App\Models\User;
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

class DashboardCalidadTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_renders_the_curricular_process_indicator(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);

        $this->get('/indicadores/calidad')
            ->assertOk()
            ->assertSee('Indicadores de calidad académica')
            ->assertSee('Gestión Curricular')
            ->assertSee('Gestión del Ingreso')
            ->assertSee('Enseñanza y Aprendizaje')
            ->assertSee('Resultados de la Formación')
            ->assertSee('I-M01.01-DPA-004')
            ->assertSee('Ver')
            ->assertDontSee('Registrar datos')
            ->assertDontSee('Valor medido')
            ->assertDontSee('Conformes')
            ->assertDontSee('Sin medición')
            ->assertDontSee('Aprobación por ciclo académico')
            ->assertDontSee('Retención y repitencia')
            ->assertDontSee('Condición laboral')
            ->assertDontSee('data-quality-chart', false);
    }

    public function test_dashboard_classifies_every_indicator_under_its_macro_process(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);

        $this->get('/indicadores/calidad')
            ->assertOk()
            ->assertSee('M01.01.02.02-FI-001')
            ->assertSee(IndicadorCompetenciasEsperadasService::CODIGO_GENERALES)
            ->assertSee('Estudiantes que logran las COMPETENCIAS GENERALES esperadas (%)')
            ->assertSee(IndicadorCompetenciasEsperadasService::CODIGO_ESPECIFICAS)
            ->assertSee('Estudiantes que logran las COMPETENCIAS ESPECÍFICAS esperadas (%)')
            ->assertSee(IndicadorDesaprobadosService::CODIGO)
            ->assertSee('Estudiantes desaprobados en cada experiencia curricular')
            ->assertSee(IndicadorDesaprobadosDosOMasVecesService::CODIGO)
            ->assertSee('Estudiantes desaprobados dos o más veces en una experiencia curricular')
            ->assertSee(IndicadorDesaprobadosPorCohorteService::CODIGO)
            ->assertSee('Estudiantes desaprobados (%) - Por promoción o cohorte y por experiencia curricular')
            ->assertSee(IndicadorTutoriaService::CODIGO_LOGRO_OBJETIVOS)
            ->assertSee('Logro de objetivos del programa de tutoría académica y apoyo pedagógico (%)')
            ->assertSee(IndicadorTutoriaService::CODIGO_SATISFACCION_ESTUDIANTE)
            ->assertSee('Satisfacción del estudiante con los programas de consejería académica y tutoría (%)')
            ->assertSee(IndicadorEgresadosService::CODIGO_TITULADOS_COHORTE)
            ->assertSee(IndicadorEgresadosService::CODIGO_TITULADOS_DOCE_MESES)
            ->assertSee(IndicadorEgresadosService::CODIGO_EGRESADOS_PROMOCION)
            ->assertSee(IndicadorEgresadosService::CODIGO_EMPLEABILIDAD)
            ->assertSee(IndicadorEgresadosService::CODIGO_OBJETIVOS_EDUCACIONALES)
            ->assertSee(IndicadorEgresadosService::CODIGO_SATISFACCION_EGRESADOS)
            ->assertSee(IndicadorEgresadosService::CODIGO_SATISFACCION_EMPLEADORES)
            ->assertSee('Satisfacción de los empleadores (%)');
    }

    public function test_dashboard_omits_retired_indicators_and_removed_summary_sections(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        IndicadorMaestro::factory()->create([
            'codigo' => 'M01.05-DCU-FI-001',
            'nombre' => 'Egresados titulados',
            'macro_proceso' => 'Resultados de la Formación',
        ]);
        IndicadorMaestro::factory()->create([
            'codigo' => 'M01.05-DCU-FI-002',
            'nombre' => 'Egresados laborando',
            'macro_proceso' => 'Resultados de la Formación',
        ]);

        $this->get('/indicadores/calidad')
            ->assertSee('Satisfacción de los empleadores (%)')
            ->assertSee('M01.05/PG-I5')
            ->assertDontSee('Egresados titulados</h3>', false)
            ->assertDontSee('M01.05-DCU-FI-001')
            ->assertDontSee('Egresados laborando')
            ->assertDontSee('M01.05-DCU-FI-002')
            ->assertDontSee('Cobertura de acompañamiento estudiantil')
            ->assertDontSee('Resultados de egresados')
            ->assertDontSee('Planes de mejora');
    }

    public function test_cohort_failures_card_uses_its_official_document_when_available(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        Document::query()->create([
            'title' => 'Estudiantes desaprobados (%) - Por promoción o cohorte  y por expiencia curricular',
            'document_type' => Document::TIPO_CALIDAD,
            'section' => 'Enseñanza y Aprendizaje',
            'drive_url' => 'https://docs.google.com/spreadsheets/d/documento-indicador-i5/edit',
            'publication_date' => '2026-03-01',
        ]);

        Livewire::test(DashboardCalidad::class)
            ->assertSee(IndicadorDesaprobadosPorCohorteService::CODIGO)
            ->assertSeeHtml('data-title="Estudiantes desaprobados (%) - Por promoción o cohorte  y por expiencia curricular"')
            ->assertSee('Ver documento');
    }

    public function test_competency_cards_use_their_official_documents_when_available(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);

        foreach ([
            'Estudiantes que logran las COMPETENCIAS GENERALES esperadas',
            'Estudiantes que logran las COMPETENCIAS ESPECIFICAS  esperadas (%)',
        ] as $titulo) {
            Document::query()->create([
                'title' => $titulo,
                'document_type' => Document::TIPO_CALIDAD,
                'section' => 'Enseñanza y Aprendizaje',
                'drive_url' => 'https://docs.google.com/spreadsheets/d/'.md5($titulo).'/edit',
                'publication_date' => '2026-03-01',
            ]);
        }

        Livewire::test(DashboardCalidad::class)
            ->assertSeeHtml('data-title="Estudiantes que logran las COMPETENCIAS GENERALES esperadas"')
            ->assertSeeHtml('data-title="Estudiantes que logran las COMPETENCIAS ESPECIFICAS  esperadas (%)"');
    }

    public function test_director_can_record_an_improvement_plan_for_a_critical_measurement(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $director = User::factory()->create();
        $director->givePermissionTo('indicator.analyze');
        $programa = ProgramaEstudio::query()->firstOrFail();
        $indicador = IndicadorMaestro::query()->where('codigo', 'M01.01.02.02-FI-002')->firstOrFail();
        $medicion = IndicadorMedicion::query()->updateOrCreate([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
        ], [
            'valor_medido' => 18,
            'meta_programada' => 10,
            'estado_cumplimiento' => 'CRITICO',
        ]);

        Livewire::actingAs($director)->test(DashboardCalidad::class)
            ->set('periodoAcademico', '2026-I')
            ->call('editarPlan', $indicador->id)
            ->set('analisisCausas', 'Brechas detectadas en cursos de primer año.')
            ->set('accionesMejora', 'Aplicar reforzamiento y seguimiento quincenal.')
            ->set('responsableId', $director->id)
            ->set('fechaLimite', '2026-06-30')
            ->call('guardarPlan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('indicadores_mediciones', [
            'id' => $medicion->id,
            'analisis_causas' => 'Brechas detectadas en cursos de primer año.',
            'acciones_mejora' => 'Aplicar reforzamiento y seguimiento quincenal.',
        ]);
        $this->assertDatabaseHas('acciones_mejora_indicadores', [
            'indicador_medicion_id' => $medicion->id,
            'responsable_id' => $director->id,
            'estado' => 'PENDIENTE',
        ]);
    }

    public function test_dashboard_exports_current_period_as_pdf(): void
    {
        $this->seed(IndicadoresSeeder::class);
        $programa = ProgramaEstudio::query()->firstOrFail();

        $this->get(route('quality-indicators.export', [
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
        ]))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_observed_measurement_requires_analysis_without_forcing_an_action(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $responsable = User::factory()->create();
        $responsable->givePermissionTo('indicator.analyze');
        $programa = ProgramaEstudio::query()->firstOrFail();
        $indicador = IndicadorMaestro::query()->where('codigo', 'M01.01.02.02-FI-001')->firstOrFail();
        $medicion = IndicadorMedicion::query()->updateOrCreate([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
        ], [
            'valor_medido' => 88,
            'meta_programada' => 90,
            'estado_cumplimiento' => 'OBSERVADO',
        ]);

        Livewire::actingAs($responsable)->test(DashboardCalidad::class)
            ->set('periodoAcademico', '2026-I')
            ->call('editarPlan', $indicador->id)
            ->set('analisisCausas', 'Descenso temporal de estudiantes matriculados.')
            ->call('guardarPlan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('indicadores_mediciones', ['id' => $medicion->id, 'acciones_mejora' => null]);
        $this->assertDatabaseMissing('acciones_mejora_indicadores', ['indicador_medicion_id' => $medicion->id]);
    }
}
