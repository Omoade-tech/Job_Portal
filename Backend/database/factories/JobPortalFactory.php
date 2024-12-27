<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
            'salary' => $this->faker->numberBetween(150000, 1500000), 
            'description' => $this->faker->paragraphs(3, true),
            'location' => $this->faker->city() . ', ' . $this->faker->state(), 
            'responsibility' => $this->faker->sentences(3, true), 
        ];
    }
}
