<?php

namespace Database\Seeders;

use App\Models\Developer;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Default database seeds for local and demo environments.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Developer::factory(10)->create();

        Developer::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
