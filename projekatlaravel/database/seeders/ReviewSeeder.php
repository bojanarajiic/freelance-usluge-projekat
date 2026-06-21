<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Review;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Contract::where('status', 'completed')->each(function ($contract) {
            Review::factory()->create([
                'project_id'  => $contract->project_id,
                'reviewer_id' => $contract->project->client_id,
                'reviewee_id' => $contract->freelancer_id,
            ]);
        });
    }
}
