<?php

namespace Database\Seeders;

use App\Models\RecordedSession;
use App\Models\VirustotalScan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 5 recorded sessions, and for each session, generate 3 to 7 random scans
        RecordedSession::factory(5)
            ->has(
                VirustotalScan::factory()->count(rand(3, 7)),
                'scans' // matches the relationship method name in RecordedSession model
            )
            ->create();
    }
}