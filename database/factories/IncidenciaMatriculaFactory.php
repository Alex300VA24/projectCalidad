<?php

namespace Database\Factories;

use App\Models\IncidenciaMatricula;
use App\Models\ProgramaEstudio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncidenciaMatricula>
 */
class IncidenciaMatriculaFactory extends Factory
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
            'periodo_academico' => '2026-I',
            'descripcion' => fake()->sentence(),
            'estado' => 'REPORTADA',
            'resuelta_en' => null,
        ];
    }
}
