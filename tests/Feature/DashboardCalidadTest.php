<?php

namespace Tests\Feature;

use App\Livewire\Indicadores\DashboardCalidad;
use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\ProgramaEstudio;
use App\Models\User;
use Database\Seeders\IndicadoresSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardCalidadTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_renders_the_official_indicator_catalog(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);

        $this->get('/indicadores/calidad')
            ->assertOk()
            ->assertSee('Indicadores de calidad académica')
            ->assertSee('M01.01.02.02-FI-001');
    }

    public function test_director_can_record_an_improvement_plan_for_a_critical_measurement(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $director = User::factory()->create();
        $director->givePermissionTo('indicator.analyze');
        $programa = ProgramaEstudio::query()->firstOrFail();
        $indicador = IndicadorMaestro::query()->where('codigo', 'M01.01.02.02-FI-002')->firstOrFail();
        $medicion = IndicadorMedicion::factory()->create([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
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
        $medicion = IndicadorMedicion::factory()->create([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
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
