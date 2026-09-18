<?php

namespace Database\Factories;

use App\Models\AccionMejoraIndicador;
use App\Models\IndicadorMedicion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccionMejoraIndicador>
 */
class AccionMejoraIndicadorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'indicador_medicion_id' => IndicadorMedicion::factory(),
            'descripcion' => fake()->sentence(),
            'responsable_id' => User::factory(),
            'fecha_limite' => fake()->dateTimeBetween('now', '+3 months'),
            'estado' => 'PENDIENTE',
            'seguimiento' => null,
            'evidencia_url' => null,
            'verificada_por' => null,
            'cerrada_en' => null,
        ];
    }
}
