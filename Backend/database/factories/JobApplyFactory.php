<?php

namespace Database\Factories;

use App\Models\JobPortal;
use App\Models\JobSeeker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApply>
 */
class JobApplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [ 
            'coverLetter' => fake()->paragraph(3), 
            'resume' => fake()->url(), 
            'job_portals_id' => JobPortal::inRandomOrder()->first()->id, 
            'job_seekers_id' => JobSeeker::inRandomOrder()->first()->id, 
        ];
    }
}
