<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Contract> */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-10 days', '+5 days');
        $end   = (clone $start)->modify('+' . $this->faker->numberBetween(5, 60) . ' days');

        $status = $this->faker->randomElement([
            Contract::STATUS_ACTIVE ?? 'active',
            Contract::STATUS_COMPLETED ?? 'completed',
            Contract::STATUS_CANCELLED ?? 'cancelled',
        ]);

        return [
            'project_id'     => Project::factory(),
            'freelancer_id'  => User::factory()->state(['role' => User::ROLE_FREELANCER ?? 'freelancer']),
            'agreed_amount'  => $this->faker->randomFloat(2, 200, 10000),
            'status'         => $status,
            'start_at'       => $start,
            'end_at'         => $end,
        ];
    }
}
