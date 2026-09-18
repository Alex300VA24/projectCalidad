<?php

namespace Database\Factories;

use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\ProgramaEstudio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndicadorMedicion>
 */
class IndicadorMedicionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'indicador_id' => IndicadorMaestro::factory(),
            'programa_estudio_id' => ProgramaEstudio::factory(),
            'periodo_academico' => '2026-I',
            'valor_medido' => 82.5,
            'meta_programada' => 80,
            'estado_cumplimiento' => 'CONFORME',
            'analisis_causas' => null,
            'acciones_mejora' => null,
            'registrado_por' => null,
            'consolidada_en' => null,
            'datos_fuente' => null,
        ];
    }
}
