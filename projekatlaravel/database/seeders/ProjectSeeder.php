<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = User::where('role', 'client')->get();

        foreach ($clients as $client) {
            Project::factory()->count(3)->create([
                'client_id' => $client->id,
            ]);
        }
    }
}
