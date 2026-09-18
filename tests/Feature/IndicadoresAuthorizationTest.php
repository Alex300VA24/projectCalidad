<?php

namespace Tests\Feature;

use App\Livewire\Indicadores\DashboardCalidad;
use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\ProgramaEstudio;
use Database\Seeders\IndicadoresSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndicadoresAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_user_can_view_dashboard_but_cannot_record_analysis(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, IndicadoresSeeder::class]);
        $programa = ProgramaEstudio::query()->firstOrFail();
        $indicador = IndicadorMaestro::query()->where('codigo', 'M01.01.02.02-FI-002')->firstOrFail();
        IndicadorMedicion::factory()->create([
            'indicador_id' => $indicador->id,
            'programa_estudio_id' => $programa->id,
            'periodo_academico' => '2026-I',
            'estado_cumplimiento' => 'CRITICO',
        ]);

        $this->get('/indicadores/calidad')->assertOk();

        Livewire::test(DashboardCalidad::class)
            ->set('periodoAcademico', '2026-I')
            ->call('editarPlan', $indicador->id)
            ->assertForbidden();
    }
}
