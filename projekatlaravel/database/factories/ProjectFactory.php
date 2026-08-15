<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement([
            Project::STATUS_OPEN ?? 'open',
            Project::STATUS_IN_PROGRESS ?? 'in_progress',
            Project::STATUS_COMPLETED ?? 'completed',
            Project::STATUS_CANCELLED ?? 'cancelled',
        ]);

        return [
          
            'client_id'   => User::factory()->state(['role' => User::ROLE_CLIENT ?? 'client']),
            'title'       => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(),
         
            'status'      => $status,
             
        ];
    }
}
