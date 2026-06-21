<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Skill> */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'PHP','Laravel','MySQL','JavaScript','React','Vue',
                'Node.js','Docker','Git','CI/CD','REST','Linux'
            ]),
        ];
    }
}
