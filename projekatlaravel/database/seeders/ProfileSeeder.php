<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         // kreira profil za svakog freelancera
        User::where('role', 'freelancer')->get()->each(function ($user) {
            Profile::factory()->create(['user_id' => $user->id]);
        });
    }
}
