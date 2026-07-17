<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProposalSeeder extends Seeder
{
    public function run(): void
    {
        // Ako nema freelancera, napravi ih nekoliko da seeder uvek radi
        $freelancers = User::where('role', 'freelancer')->get();
        if ($freelancers->isEmpty()) {
            $freelancers = User::factory()
                ->count(5)
                ->state(['role' => 'freelancer'])
                ->create();
        }

        Project::all()->each(function (Project $project) use ($freelancers) {
            // po želji: isključi klijenta projekta iz kandidata
            $candidates = $freelancers->when(
                $project->client_id,
                fn ($col) => $col->where('id', '!=', $project->client_id)
            );

            // koliko želimo pokušaja
            $want = random_int(2, 4);
            $take = min($want, $candidates->count());

            // umesto random(n) koristimo shuffle()->take(n) da ne baca izuzetak
            $pick = $candidates->shuffle()->take($take);

            foreach ($pick as $freelancer) {
                Proposal::factory()->create([
                    'project_id'    => $project->id,
                    'freelancer_id' => $freelancer->id,
                ]);
            }
        });
    }
}
