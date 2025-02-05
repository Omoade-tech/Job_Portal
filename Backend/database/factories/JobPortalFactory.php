<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employer;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobPortal>
 */
class JobPortalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employer_id' => Employer::factory(),
            'companyLogo' => $this->faker->imageUrl(100, 100, 'technology', true, 'Tech Logo'), 
            'companyName' => $this->faker->company() . ' Tech', 
            'contract' => $this->faker->randomElement(['Full-time', 'Part-time', 'Contract', 'Temporary']), 
            'post' => $this->faker->randomElement([
                'Software Engineer',
                'Web Developer',
                'Data Scientist',
                'Frontend Developer',
                'Backend Developer',
                'DevOps Engineer',
                'Cloud Engineer',
                'Full-Stack Developer',
            ]), 
            'salary' => '$' . number_format($this->faker->randomFloat(2, 150000, 1500000), 2), // Add $ and format as a decimal value
            'description' => $this->faker->paragraphs(3, true),
            'location' => $this->faker->city() . ', ' . $this->faker->state(), 
            'responsibility' => $this->faker->sentences(3, true), 
        ];
    }
}
