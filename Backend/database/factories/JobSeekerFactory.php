<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobSeeker>
 */
class JobSeekerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $password = bcrypt('password'); 

        return [
            'role' => 'jobseeker', 
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'), 
            'phoneNumber' => $this->faker->phoneNumber(),
            'age' => $this->faker->numberBetween(25, 60),
            'sex' => $this->faker->randomElement(['male', 'female']),
            'status' => $this->faker->randomElement(['Single', 'Married']),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
        ];
    }
}
