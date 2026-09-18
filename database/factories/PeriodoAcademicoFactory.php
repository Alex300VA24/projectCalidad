<?php

namespace Database\Factories;

use App\Models\PeriodoAcademico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodoAcademico>
 */
class PeriodoAcademicoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $anio = fake()->numberBetween(2020, 2035);
        $semestre = fake()->randomElement(['I', 'II']);

        return [
            'codigo' => "{$anio}-{$semestre}",
            'fecha_inicio' => "{$anio}-".($semestre === 'I' ? '01-01' : '07-01'),
            'fecha_fin' => "{$anio}-".($semestre === 'I' ? '06-30' : '12-31'),
            'activo' => true,
        ];
    }
}
