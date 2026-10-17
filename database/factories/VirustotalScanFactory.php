<?php

namespace Database\Factories;

use App\Models\RecordedSession;
use App\Models\VirustotalScan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VirustotalScan>
 */
class VirustotalScanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => RecordedSession::factory(),
            'original_filename' => $this->faker->word().'.'.$this->faker->fileExtension(),
            'file_hash' => $this->faker->sha256(),
            'file_path' => '/var/www/uploads/'.$this->faker->uuid().'.bin',
            'file_size' => $this->faker->numberBetween(1024, 10485760), // Size in bytes
            'analysis_id' => 'u-'.$this->faker->sha256(),
            'status' => $this->faker->randomElement(['queued', 'pending', 'completed', 'error']),
            'malicious' => $this->faker->numberBetween(0, 5),
            'suspicious' => $this->faker->numberBetween(0, 2),
            'harmless' => $this->faker->numberBetween(20, 70),
            'undetected' => $this->faker->numberBetween(0, 5),
            'scan_date' => now(),
            'submitted_at' => now(),
            'raw_response' => ['data' => ['attributes' => ['status' => 'completed']]],
        ];
    }
}
