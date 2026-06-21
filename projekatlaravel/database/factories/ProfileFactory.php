<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Profile> */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'bio'           => $this->faker->optional()->paragraph(),
            'github_url'    => $this->faker->optional()->url(),
            'portfolio_url' => $this->faker->optional()->url(),
            'hourly_rate'   => $this->faker->optional()->numberBetween(10, 150),
            'location'      => $this->faker->optional()->city(),
        ];
    }
}
