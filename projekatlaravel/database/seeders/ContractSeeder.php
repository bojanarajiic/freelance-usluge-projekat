<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Proposal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       // za svaki projekat prihvati jednu ponudu i napravi ugovor
        Proposal::all()->groupBy('project_id')->each(function ($proposals) {
            $accepted = $proposals->random();
            $accepted->update(['status' => 'accepted']);

            Contract::factory()->create([
                'project_id'    => $accepted->project_id,
                'freelancer_id' => $accepted->freelancer_id,
                'agreed_amount' => $accepted->amount,
                'status'        => 'active',
            ]);
        });
    }
}
