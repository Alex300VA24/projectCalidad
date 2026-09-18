<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Matricula;
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

    public function test_matriculas_page_renders_and_allows_creating_matricula_and_incidencia(): void
    {
        $programa = ProgramaEstudio::first();
        $curso = Course::create(['name' => 'Ingeniería de Software', 'code' => 'IS-101', 'credits' => 4]);
        $estudiante = User::factory()->create();

        $response = $this->get(route('matriculas.index'));
        $response->assertOk();
        $response->assertSee('Gestión de Matrícula e Incidencias');
        $response->assertSee('MAT-01');

        // Store Matricula
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

        // Store Incidencia
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

        // Verify that CalculadorIndicadoresService calculates repetition and incidents resolution
        $calculador = app(CalculadorIndicadoresService::class);
        $valores = $calculador->calcularValores($programa, '2026-I');

        $this->assertNotNull($valores[CalculadorIndicadoresService::CODIGO_REPITENCIA]);
        $this->assertEquals(100.0, $valores[CalculadorIndicadoresService::CODIGO_REPITENCIA]);
        $this->assertEquals(100.0, $valores[CalculadorIndicadoresService::CODIGO_INCIDENCIAS]);
    }
}
