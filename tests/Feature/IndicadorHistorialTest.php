<?php

namespace Tests\Feature;

use App\Livewire\Indicadores\IndicadorHistorial;
use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\ProgramaEstudio;
use App\Models\User;
use App\Services\CalculadorIndicadoresService;
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
        IndicadorMedicion::factory()->create([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2025-I',
            'valor_medido' => 80,
            'meta_programada' => 90,
            'estado_cumplimiento' => 'OBSERVADO',
        ]);
        IndicadorMedicion::factory()->create([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2025-II',
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
}
