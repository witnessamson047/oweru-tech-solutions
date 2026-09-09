<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageExclusionSeeder extends Seeder
{
    public function run(): void
    {
        $exclusions = [
            ['description' => 'Custom ERP or accounting system integration', 'sort_order' => 1],
            ['description' => 'Government regulatory compliance audits', 'sort_order' => 2],
            ['description' => 'Physical branding or print design services', 'sort_order' => 3],
            ['description' => 'Social media account management', 'sort_order' => 4],
            ['description' => 'Third-party API integration fees', 'sort_order' => 5],
            ['description' => 'Domain name registration or transfer', 'sort_order' => 6],
            ['description' => 'Content writing or copywriting beyond initial setup', 'sort_order' => 7],
            ['description' => 'Stock photography or videography', 'sort_order' => 8],
        ];

        foreach ($exclusions as $exclusion) {
            DB::table('package_exclusions')->updateOrInsert(
                ['description' => $exclusion['description']],
                array_merge($exclusion, ['active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
