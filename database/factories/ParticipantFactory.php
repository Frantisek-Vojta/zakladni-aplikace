<?php

namespace Database\Factories;

use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            // unique() zajisti, ze dva ucastnici ve stejnem testu nedostanou
            // stejny e-mail (email je v databazi unikatni)
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
