<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Matricula;
use App\Models\ProgramaEstudio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matricula>
 */
class MatriculaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'programa_estudio_id' => ProgramaEstudio::factory(),
            'estudiante_id' => User::factory(),
            'curso_id' => Course::factory(),
            'periodo_academico' => '2026-I',
            'ciclo_academico' => fake()->numberBetween(1, 10),
            'numero_matricula' => 1,
            'estado_matricula' => 'CONFIRMADA',
            'estado_resultado' => 'CURSANDO',
        ];
    }
}
