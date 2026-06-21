<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProposalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $freelancers = User::where('role', 'freelancer')->get();

        Project::all()->each(function ($project) use ($freelancers) {
            $freelancers->random(rand(2, 4))->each(function ($freelancer) use ($project) {
                Proposal::factory()->create([
                    'project_id'    => $project->id,
                    'freelancer_id' => $freelancer->id,
                ]);
            });
        });
    }
}
