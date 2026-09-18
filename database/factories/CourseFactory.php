<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Curriculum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'curriculum_id' => Curriculum::factory(),
            'code' => fake()->unique()->bothify('CUR-###'),
            'name' => fake()->words(3, true),
            'credits' => fake()->numberBetween(2, 5),
        ];
    }
}
