<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\IncidenciaMatricula;
use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Models\Syllabus;
use App\Models\User;
use App\Services\CalculadorIndicadoresService;
use App\Services\IndicadoresCacheService;
use Database\Seeders\IndicadoresSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CalculadorIndicadoresServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_calculates_and_classifies_enrollment_indicators(): void
    {
        $this->seed(IndicadoresSeeder::class);
        $programa = ProgramaEstudio::query()->firstOrFail();
        $curso = Course::factory()->create();
        $estudiantes = User::factory()->count(10)->create();

        foreach ($estudiantes as $estudiante) {
            Matricula::factory()->create([
                'programa_estudio_id' => $programa->id,
                'estudiante_id' => $estudiante->id,
                'curso_id' => $curso->id,
                'periodo_academico' => '2025-II',
            ]);
        }

        foreach ($estudiantes->take(9) as $indice => $estudiante) {
            Matricula::factory()->create([
                'programa_estudio_id' => $programa->id,
                'estudiante_id' => $estudiante->id,
                'curso_id' => $curso->id,
                'periodo_academico' => '2026-I',
                'numero_matricula' => $indice < 2 ? 2 : 1,
            ]);
        }

        IncidenciaMatricula::factory()->create([
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
            'estado' => 'RESUELTA',
        ]);
        IncidenciaMatricula::factory()->create([
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
            'estado' => 'REPORTADA',
        ]);

        $servicio = app(CalculadorIndicadoresService::class);
        $valores = $servicio->calcularValores($programa, '2026-I');
        $servicio->calcularYConsolidar($programa, '2026-I');

        $this->assertSame(90.0, $valores[CalculadorIndicadoresService::CODIGO_RETENCION]);
        $this->assertSame(22.22, $valores[CalculadorIndicadoresService::CODIGO_REPITENCIA]);
        $this->assertSame(50.0, $valores[CalculadorIndicadoresService::CODIGO_INCIDENCIAS]);
        $this->assertDatabaseHas('indicadores_mediciones', [
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
            'estado_cumplimiento' => 'CONFORME',
            'valor_medido' => 90,
        ]);

        $repitencia = IndicadorMedicion::query()
            ->whereHas('indicador', fn ($query) => $query->where('codigo', CalculadorIndicadoresService::CODIGO_REPITENCIA))
            ->firstOrFail();

        $this->assertSame('CRITICO', $repitencia->estado_cumplimiento);
    }

    public function test_classifies_lower_values_as_conforming_for_inverse_indicators(): void
    {
        $indicador = IndicadorMaestro::factory()->make([
            'meta_institucional' => 10,
            'nivel_critico' => 15,
            'sentido_meta' => IndicadorMaestro::SENTIDO_MENOR_IGUAL,
        ]);

        $this->assertSame('CONFORME', $indicador->clasificar(8));
        $this->assertSame('OBSERVADO', $indicador->clasificar(12));
        $this->assertSame('CRITICO', $indicador->clasificar(15));
        $this->assertSame('CRITICO', $indicador->clasificar(16));
    }

    public function test_calculates_visa_percentage_from_implemented_courses(): void
    {
        $this->seed(IndicadoresSeeder::class);
        $programa = ProgramaEstudio::query()->firstOrFail();
        $periodo = PeriodoAcademico::query()->where('codigo', '2026-I')->firstOrFail();
        $teacher = User::factory()->create();
        $courses = Course::factory()->count(10)->create();

        foreach ($courses as $index => $course) {
            Matricula::factory()->create([
                'programa_estudio_id' => $programa->id,
                'estudiante_id' => User::factory(),
                'curso_id' => $course->id,
                'periodo_academico' => '2026-I',
            ]);
            Syllabus::query()->create([
                'programa_estudio_id' => $programa->id,
                'periodo_academico_id' => $periodo->id,
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'academic_period' => '2026-I',
                'status' => $index < 8 ? 'visado' : 'draft',
            ]);
        }

        $calculador = app(CalculadorIndicadoresService::class);
        $valor = $calculador->calcularValores($programa, '2026-I')[CalculadorIndicadoresService::CODIGO_SILABOS];
        $indicador = IndicadorMaestro::query()->where('codigo', CalculadorIndicadoresService::CODIGO_SILABOS)->firstOrFail();

        $this->assertSame(80.0, $valor);
        $this->assertSame('CONFORME', $indicador->clasificar($valor));

        $silabo = Syllabus::query()->where('status', 'visado')->firstOrFail();
        $silabo->update(['status' => 'draft']);
        app(IndicadoresCacheService::class)->olvidarModelo($silabo);
        $valor = $calculador->calcularValores($programa, '2026-I')[CalculadorIndicadoresService::CODIGO_SILABOS];

        $this->assertSame(70.0, $valor);
        $this->assertSame('NO_CONFORME', $indicador->clasificar($valor));
    }

    public function test_classifies_retention_boundaries_and_calculates_incident_resolution(): void
    {
        $this->seed(IndicadoresSeeder::class);
        $programa = ProgramaEstudio::query()->firstOrFail();
        $retencion = IndicadorMaestro::query()->where('codigo', CalculadorIndicadoresService::CODIGO_RETENCION)->firstOrFail();

        $this->assertSame('CONFORME', $retencion->clasificar(90));
        $this->assertSame('OBSERVADO', $retencion->clasificar(80));
        $this->assertSame('CRITICO', $retencion->clasificar(79));

        IncidenciaMatricula::factory()->count(95)->create(['programa_estudio_id' => $programa->id, 'periodo_academico' => '2026-I', 'estado' => 'RESUELTA']);
        IncidenciaMatricula::factory()->count(5)->create(['programa_estudio_id' => $programa->id, 'periodo_academico' => '2026-I', 'estado' => 'EN_PROCESO']);
        $valor = app(CalculadorIndicadoresService::class)->calcularValores($programa, '2026-I')[CalculadorIndicadoresService::CODIGO_INCIDENCIAS];

        $this->assertSame(95.0, $valor);
    }

    public function test_vise_workflow_invalidates_and_updates_syllabus_indicator(): void
    {
        $this->markTestSkipped('El flujo de trámites está deshabilitado temporalmente.');

        Storage::fake('local');
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $programa = ProgramaEstudio::query()->firstOrFail();
        $periodo = PeriodoAcademico::query()->where('codigo', '2026-I')->firstOrFail();
        $course = Course::factory()->create();
        $director = User::factory()->create();
        $director->givePermissionTo('syllabus.vise');
        Matricula::factory()->create(['programa_estudio_id' => $programa->id, 'curso_id' => $course->id, 'periodo_academico' => '2026-I']);
        $syllabus = Syllabus::query()->create([
            'programa_estudio_id' => $programa->id,
            'periodo_academico_id' => $periodo->id,
            'course_id' => $course->id,
            'teacher_id' => $director->id,
            'academic_period' => '2026-I',
            'status' => 'reviewed',
        ]);
        $calculador = app(CalculadorIndicadoresService::class);
        $this->assertSame(0.0, $calculador->calcularValores($programa, '2026-I')[CalculadorIndicadoresService::CODIGO_SILABOS]);

        $this->actingAs($director)->put(route('syllabi.update', $syllabus), [
            'programa_estudio_id' => $programa->id,
            'periodo_academico_id' => $periodo->id,
            'course_id' => $course->id,
            'teacher_id' => $director->id,
            'status' => 'visado',
            'review_checklist' => json_encode(['cumple' => true]),
        ])->assertRedirect(route('syllabi.index'));

        $this->assertSame(100.0, $calculador->calcularValores($programa, '2026-I')[CalculadorIndicadoresService::CODIGO_SILABOS]);
        Storage::disk('local')->assertExists("syllabus/{$syllabus->id}/requerimiento-biblioteca.pdf");
    }
}
