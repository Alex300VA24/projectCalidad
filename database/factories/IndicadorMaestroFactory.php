<?php

namespace Database\Factories;

use App\Models\IndicadorMaestro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndicadorMaestro>
 */
class IndicadorMaestroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->bothify('FI-###'),
            'nombre' => fake()->sentence(5),
            'proceso' => 'Gestión Académica',
            'finalidad' => fake()->sentence(),
            'formula_texto' => '(Numerador / Denominador) * 100',
            'unidad_medida' => 'PORCENTAJE',
            'meta_institucional' => 80,
            'nivel_critico' => 70,
            'nivel_advertencia' => null,
            'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
            'regla_cumplimiento' => null,
            'configuracion' => null,
            'frecuencia' => 'SEMESTRAL',
            'responsable' => 'Director de Escuela',
        ];
    }
}
