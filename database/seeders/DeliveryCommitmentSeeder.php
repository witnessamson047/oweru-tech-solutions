<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryCommitmentSeeder extends Seeder
{
    public function run(): void
    {
        $commitments = [
            ['title' => 'Clear Timelines', 'description' => 'We provide fixed delivery dates before work begins.', 'sort_order' => 1],
            ['title' => 'Regular Updates', 'description' => 'Weekly progress reports during your project.', 'sort_order' => 2],
            ['title' => 'Quality Assurance', 'description' => 'Every deliverable is tested before handover.', 'sort_order' => 3],
            ['title' => 'Post-Launch Support', 'description' => '30 days of free bug fixes after delivery.', 'sort_order' => 4],
        ];

        foreach ($commitments as $commitment) {
            DB::table('delivery_commitments')->updateOrInsert(
                ['title' => $commitment['title']],
                array_merge($commitment, ['active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
