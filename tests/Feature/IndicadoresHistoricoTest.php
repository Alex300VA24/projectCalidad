<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\IndicadorMedicion;
use App\Models\Matricula;
use App\Models\ProgramaEstudio;
use App\Models\User;
use App\Services\CalculadorIndicadoresService;
use Database\Seeders\IndicadoresSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class IndicadoresHistoricoTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_retention_measurements_are_loaded_from_json(): void
    {
        $this->seed(IndicadoresSeeder::class);

        $mediciones = IndicadorMedicion::query()
            ->where('programa_estudio_id', ProgramaEstudio::query()->where('codigo', 'UNT-EP')->value('id'))
            ->whereHas('indicador', fn ($query) => $query->where('codigo', CalculadorIndicadoresService::CODIGO_RETENCION))
            ->orderBy('periodo_academico')
            ->get();

        $this->assertCount(4, $mediciones);
        $this->assertSame(['2024-II', '2025-I', '2025-II', '2026-I'], $mediciones->pluck('periodo_academico')->all());
        $this->assertSame([317, 341, 336, 368], $mediciones->map(fn (IndicadorMedicion $medicion): int => $medicion->datos_fuente['numerador'])->all());
        $this->assertSame([326, 317, 341, 336], $mediciones->map(fn (IndicadorMedicion $medicion): int => $medicion->datos_fuente['denominador'])->all());
        $this->assertSame([97.24, 107.57, 98.53, 109.52], $mediciones->map(fn (IndicadorMedicion $medicion): float => (float) $medicion->valor_medido)->all());
    }

    public function test_consolidated_period_is_not_overwritten_by_later_recalculation(): void
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
        foreach ($estudiantes->take(9) as $estudiante) {
            Matricula::factory()->create([
                'programa_estudio_id' => $programa->id,
                'estudiante_id' => $estudiante->id,
                'curso_id' => $curso->id,
                'periodo_academico' => '2026-I',
            ]);
        }

        $calculador = app(CalculadorIndicadoresService::class);
        $calculador->consolidarPeriodo($programa, '2026-I');
        Matricula::factory()->create([
            'programa_estudio_id' => $programa->id,
            'estudiante_id' => $estudiantes->last()->id,
            'curso_id' => $curso->id,
            'periodo_academico' => '2026-I',
        ]);
        $calculador->consolidarPeriodo($programa, '2026-I');

        $medicion = IndicadorMedicion::query()
            ->where('programa_estudio_id', $programa->id)
            ->where('periodo_academico', '2026-I')
            ->whereHas('indicador', fn ($query) => $query->where('codigo', CalculadorIndicadoresService::CODIGO_RETENCION))
            ->firstOrFail();

        $this->assertSame(90.0, (float) $medicion->valor_medido);
        $this->assertNotNull($medicion->consolidada_en);
    }

    public function test_retention_ignores_other_programs_periods_and_cancelled_enrolments(): void
    {
        $this->seed(IndicadoresSeeder::class);
        $programa = ProgramaEstudio::query()->firstOrFail();
        $otroPrograma = ProgramaEstudio::factory()->create();
        $curso = Course::factory()->create();
        $estudiantes = User::factory()->count(11)->create();

        foreach ($estudiantes->take(10) as $estudiante) {
            Matricula::factory()->create(['programa_estudio_id' => $programa->id, 'estudiante_id' => $estudiante->id, 'curso_id' => $curso->id, 'periodo_academico' => '2025-II']);
        }
        foreach ($estudiantes->take(9) as $estudiante) {
            Matricula::factory()->create(['programa_estudio_id' => $programa->id, 'estudiante_id' => $estudiante->id, 'curso_id' => $curso->id, 'periodo_academico' => '2026-I']);
        }
        Matricula::factory()->create(['programa_estudio_id' => $programa->id, 'estudiante_id' => $estudiantes->last()->id, 'curso_id' => $curso->id, 'periodo_academico' => '2026-I', 'estado_matricula' => 'ANULADA']);
        Matricula::factory()->create(['programa_estudio_id' => $otroPrograma->id, 'estudiante_id' => $estudiantes->last()->id, 'curso_id' => $curso->id, 'periodo_academico' => '2026-I']);

        $valor = app(CalculadorIndicadoresService::class)->calcularValores($programa, '2026-I')[CalculadorIndicadoresService::CODIGO_RETENCION];

        $this->assertSame(90.0, $valor);
    }

    public function test_zero_denominators_return_controlled_no_data_value(): void
    {
        $this->seed(IndicadoresSeeder::class);
        $programa = ProgramaEstudio::query()->firstOrFail();

        $valores = app(CalculadorIndicadoresService::class)->calcularValores($programa, '2026-II');

        $this->assertNull($valores[CalculadorIndicadoresService::CODIGO_RETENCION]);
        $this->assertNull($valores[CalculadorIndicadoresService::CODIGO_REPITENCIA]);
        $this->assertNull($valores[CalculadorIndicadoresService::CODIGO_INCIDENCIAS]);
    }

    public function test_transactional_change_invalidates_cached_current_measurement(): void
    {
        $this->seed(IndicadoresSeeder::class);
        $programa = ProgramaEstudio::query()->firstOrFail();
        $curso = Course::factory()->create();
        $primerEstudiante = User::factory()->create();
        Matricula::factory()->create(['programa_estudio_id' => $programa->id, 'estudiante_id' => $primerEstudiante->id, 'curso_id' => $curso->id, 'periodo_academico' => '2025-II']);
        Matricula::factory()->create(['programa_estudio_id' => $programa->id, 'estudiante_id' => $primerEstudiante->id, 'curso_id' => $curso->id, 'periodo_academico' => '2026-I']);
        $calculador = app(CalculadorIndicadoresService::class);

        $inicial = $calculador->calcularValores($programa, '2026-I')[CalculadorIndicadoresService::CODIGO_RETENCION];
        Matricula::factory()->create(['programa_estudio_id' => $programa->id, 'estudiante_id' => User::factory(), 'curso_id' => $curso->id, 'periodo_academico' => '2026-I']);
        $actualizado = $calculador->calcularValores($programa, '2026-I')[CalculadorIndicadoresService::CODIGO_RETENCION];

        $this->assertSame(100.0, $inicial);
        $this->assertSame(200.0, $actualizado);
    }
}
