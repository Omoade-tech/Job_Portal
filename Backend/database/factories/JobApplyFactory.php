<?php

namespace Database\Factories;

use App\Models\JobApply;
use App\Models\JobPortal;
use App\Models\JobSeeker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApply>
 */
class JobApplyFactory extends Factory
{
    protected $model = JobApply::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jobPortal = JobPortal::factory()->create();
        
        return [
            'coverLetter' => $this->faker->paragraph(3),
            'resume' => 'resumes/fake-resume-' . $this->faker->uuid . '.pdf',
            'job_title' => $jobPortal->post, // Add job title from the job portal
            'job_portals_id' => $jobPortal->id,
            'job_seekers_id' => JobSeeker::factory()->create()->id,
            'status' => $this->faker->randomElement([
                JobApply::STATUS_PENDING,
                JobApply::STATUS_ACCEPTED,
                JobApply::STATUS_REJECTED,
            ]),
        ];
    }

    /**
     * Indicate that the application is pending.
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => JobApply::STATUS_PENDING,
            ];
        });
    }

    /**
     * Indicate that the application is accepted.
     */
    public function accepted()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => JobApply::STATUS_ACCEPTED,
            ];
        });
    }

    /**
     * Indicate that the application is rejected.
     */
    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => JobApply::STATUS_REJECTED,
            ];
        });
    }
}
