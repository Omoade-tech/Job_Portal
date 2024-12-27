<?php

namespace Database\Factories;

use App\Models\reference;
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(), 
            'phoneNumber' => fake()->phoneNumber(),
            'address' => fake()->address(), 
            'coverLetter' => fake()->paragraph(3), 
            'resume' => fake()->url(), 
            'reference_id' => reference::inRandomOrder()->first()->id,
        ];
    }
}
