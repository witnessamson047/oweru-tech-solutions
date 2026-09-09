<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            ScannerCheckSeeder::class,
            ServicePackageSeeder::class,
            ServiceLineSeeder::class,
            AdditionalPackagesSeeder::class,
            CarePlanSeeder::class,
            PackageExclusionSeeder::class,
            DeliveryCommitmentSeeder::class,
            RecommendationSeeder::class,
        ]);
    }
}
