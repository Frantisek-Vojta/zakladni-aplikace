<?php

namespace Database\Factories;

use App\Models\Course;
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
            'name' => fake()->words(3, true),
            // unique() zajisti, ze dva kurzy ve stejnem testu nedostanou
            // stejny kod (code je ve databazi unikatni)
            'code' => fake()->unique()->bothify('???-###'),
            'capacity' => fake()->numberBetween(5, 30),
            'starts_at' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
        ];
    }
}
