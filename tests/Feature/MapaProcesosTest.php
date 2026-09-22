<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\ProgramaEstudio;
use App\Models\User;
use App\Services\CalculadorIndicadoresService;
use Database\Seeders\IndicadoresSeeder;
use Database\Seeders\ProcedureCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapaProcesosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProcedureCatalogSeeder::class);
        $this->seed(IndicadoresSeeder::class);
    }

    public function test_mapa_procesos_page_renders_successfully(): void
    {
        $response = $this->get(route('mapa-procesos.index'));

        $response->assertOk();
        $response->assertSee('Mapa de Procesos y Gestión Documental');
        $response->assertSee('Sección 1');
        $response->assertSee('Gestión Curricular (GC)');
        $response->assertSee('Sección 2');
        $response->assertSee('Seguimiento y Evaluación (SD/EV)');
        $response->assertSee('Sección 3');
        $response->assertSee('Ejecución del Plan Curricular (EPC)');
        $response->assertSee('Sección 4');
        $response->assertSee('Matrícula e Incidencias');
        $response->assertSee('GC-01');
        $response->assertSee('MAT-01');
        $response->assertSee('F-M01.01-DPA-009');
    }

    public function test_execution_format_lists_reports_by_semester_in_its_modal(): void
    {
        $response = $this->get(route('mapa-procesos.index'));

        $response->assertSee('M01.01.03.01-F-005');
        $response->assertSee('data-open-execution-reports', false);
        $response->assertSee('data-execution-period="2025-II"', false);
        $response->assertSee('data-execution-period="2026-I"', false);
        $response->assertSee('3 informes');
        $response->assertSee('ALGORITMOS Y PROGRAMACIÓN');
        $response->assertSee('YENNY SIFUENTES DIAZ');
        $response->assertSee('https://drive.google.com/file/d/1xMWYd2LvkVx6QEIqmrrUAZLXeOoAp2xg/view?usp=sharing');
        $response->assertSee('data-preview="https://drive.google.com/file/d/1xMWYd2LvkVx6QEIqmrrUAZLXeOoAp2xg/preview"', false);
        $response->assertSee('data-preview="https://drive.google.com/file/d/1PjKtE2RzmCg45yITC815qKiMcbzPMJfR/preview"', false);
        $response->assertSee('Visualizar documento');
        $response->assertDontSee('Abrir archivo');
        $response->assertSee('Sin informes registrados');
        $response->assertViewHas('executionReportsByPeriod', fn (array $reports): bool => count($reports['2025-II']) === 0
            && count($reports['2026-I']) === 3
            && $reports['2026-I'][0]['grupo'] === '1');
    }

    public function test_consolidated_execution_format_lists_spreadsheets_by_semester_in_its_modal(): void
    {
        $response = $this->get(route('mapa-procesos.index'));

        $response->assertSee('data-open-execution-reports="M01.01.03.01-F-013"', false);
        $response->assertSee('data-execution-reports-modal="M01.01.03.01-F-013"', false);
        $response->assertSee('Consolidado de la Ejecución de la Asignatura');
        $response->assertSee('data-preview="https://drive.google.com/file/d/1LjuHRatePDURqF9H5817Gt6EpFAGBwdk1av7iOsjtq0/preview"', false);
        $response->assertSee('data-preview="https://drive.google.com/file/d/1ZykIT62vdKGm4gstga5wCUHTJ-sbBymeDT6O50avoB4/preview"', false);
        $response->assertViewHas('consolidatedReportsByPeriod', fn (array $reports): bool => count($reports['2025-II']) === 1
            && count($reports['2026-I']) === 1
            && $reports['2025-II'][0]['detail'] === 'Semestre 2025-II'
            && $reports['2026-I'][0]['detail'] === 'Semestre 2026-I');
    }

    public function test_reference_format_lists_documents_by_student_in_its_modal(): void
    {
        $response = $this->get(route('mapa-procesos.index'));

        $response->assertSee('F.M01.04-DDA/PG-06');
        $response->assertSee('data-open-student-references', false);
        $response->assertSee('data-student-references-modal hidden', false);
        $response->assertSee('data-student-reference="1" aria-expanded="false" aria-controls="student-reference-files-1"', false);
        $response->assertSee('data-student-reference-panel="1" aria-label="Archivos de Dylan Niklas Jara Solorzano" hidden', false);
        $response->assertSee('Dylan Niklas Jara Solorzano');
        $response->assertSee('Programacion Cita');
        $response->assertSee('Contrareferencia');
        $response->assertSee('data-preview="https://drive.google.com/file/d/1outzYz_Tpkm1Ho7nsCft6COorgTuaGuu/preview"', false);
        $response->assertSee('data-preview="https://drive.google.com/file/d/1gcjEiWzi1E5-y_pMlznqBeimjEECmjqc/preview"', false);
        $response->assertViewHas('studentReferences', fn (array $references): bool => count($references) === 1
            && count($references['Dylan Niklas Jara Solorzano']) === 2
            && $references['Dylan Niklas Jara Solorzano'][0]['title'] === 'Programacion Cita'
            && $references['Dylan Niklas Jara Solorzano'][1]['title'] === 'Contrareferencia');
    }

    public function test_matriculas_page_renders_and_allows_creating_matricula_and_incidencia(): void
    {
        $this->markTestSkipped('El flujo de trámites está deshabilitado temporalmente.');

        $programa = ProgramaEstudio::first();
        $curso = Course::create(['name' => 'Ingeniería de Software', 'code' => 'IS-101', 'credits' => 4]);
        $estudiante = User::factory()->create();

        $response = $this->get(route('matriculas.index'));
        $response->assertOk();
        $response->assertSee('Gestión de Matrícula e Incidencias');
        $response->assertSee('MAT-01');

        $postResponse = $this->post(route('matriculas.store'), [
            'programa_estudio_id' => $programa->id,
            'estudiante_id' => $estudiante->id,
            'curso_id' => $curso->id,
            'periodo_academico' => '2026-I',
            'ciclo_academico' => 5,
            'numero_matricula' => 2,
            'estado_matricula' => 'CONFIRMADA',
            'estado_resultado' => 'APROBADO',
        ]);

        $postResponse->assertRedirect();
        $this->assertDatabaseHas('matriculas', [
            'estudiante_id' => $estudiante->id,
            'curso_id' => $curso->id,
            'periodo_academico' => '2026-I',
            'numero_matricula' => 2,
        ]);

        $incidenciaResponse = $this->post(route('incidencias-matricula.store'), [
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
            'descripcion' => 'Cruce de horarios en curso de especialidad.',
            'estado' => 'RESUELTA',
        ]);

        $incidenciaResponse->assertRedirect();
        $this->assertDatabaseHas('incidencias_matricula', [
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
            'estado' => 'RESUELTA',
        ]);

        $calculador = app(CalculadorIndicadoresService::class);
        $valores = $calculador->calcularValores($programa, '2026-I');

        $this->assertNotNull($valores[CalculadorIndicadoresService::CODIGO_REPITENCIA]);
        $this->assertEquals(100.0, $valores[CalculadorIndicadoresService::CODIGO_REPITENCIA]);
        $this->assertEquals(100.0, $valores[CalculadorIndicadoresService::CODIGO_INCIDENCIAS]);
    }

    public function test_process_map_does_not_link_to_disabled_procedures(): void
    {
        $this->get(route('mapa-procesos.index'))
            ->assertOk()
            ->assertDontSee('Todos los trámites')
            ->assertDontSee('href="/tramites', false);
    }
}
