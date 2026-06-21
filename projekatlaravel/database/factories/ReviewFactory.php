<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Review> */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'reviewer_id' => User::factory(),
            'reviewee_id' => User::factory(),
            'rating'      => $this->faker->numberBetween(1, 5),
            'comment'     => $this->faker->optional()->sentence(),
        ];
    }
}
