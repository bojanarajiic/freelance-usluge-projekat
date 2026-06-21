<?php

namespace Database\Factories;

use App\Models\Proposal;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Proposal> */
class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement([
            Proposal::STATUS_PENDING  ?? 'pending',
            Proposal::STATUS_ACCEPTED ?? 'accepted',
            Proposal::STATUS_REJECTED ?? 'rejected',
        ]);

        return [
            'project_id'    => Project::factory(),
            'freelancer_id' => User::factory()->state(['role' => User::ROLE_FREELANCER ?? 'freelancer']),
            'amount'        => $this->faker->randomFloat(2, 100, 5000),
            'delivery_days' => $this->faker->numberBetween(3, 60),
            'cover_letter'  => $this->faker->optional()->paragraph(3),
            'status'        => $status,
        ];
    }
}
