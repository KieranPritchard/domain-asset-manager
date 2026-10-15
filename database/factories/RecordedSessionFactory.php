<?php

namespace Database\Factories;

use App\Models\RecordedSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecordedSession>
 */
class RecordedSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'host' => $this->faker->ipv4(),
            'file_count' => $this->faker->numberBetween(1, 10),
        ];
    }
}
