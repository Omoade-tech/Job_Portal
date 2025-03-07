<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed the User table
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Call additional seeders
        $this->call([
            AdminSeeder::class,
            EmployerSeeder::class,
            JobPortalSeeder::class,
            JobSeekerSeeder::class,
            JobApplySeeder::class,

           
        ]);
    }
}
