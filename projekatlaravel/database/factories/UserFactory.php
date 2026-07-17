<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $role = $this->faker->randomElement([
            User::ROLE_CLIENT ?? 'client',
            User::ROLE_FREELANCER ?? 'freelancer',
        ]);

        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => bcrypt('password'), // demo lozinka
            'remember_token'    => Str::random(10),

            // polja iz tvog User modela prema ranijem kontekstu
            'role'        => $role,
           
        ];
    }
}
