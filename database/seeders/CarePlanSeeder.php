<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarePlan;

class CarePlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'Basic Care', 'slug' => 'basic-care', 'description' => 'Monthly hosting, SSL renewal, backups, and security monitoring. Up to 2 hours of minor updates per month.', 'price_tzs' => 150000, 'price_usd' => 60],
            ['name' => 'Standard Care', 'slug' => 'standard-care', 'description' => 'Everything in Basic plus content updates, performance monitoring, monthly reports, and up to 5 hours of changes per month.', 'price_tzs' => 400000, 'price_usd' => 160, 'is_featured' => true],
            ['name' => 'Premium Care', 'slug' => 'premium-care', 'description' => 'Everything in Standard plus priority support, SEO monitoring, A/B testing, analytics insights, and unlimited minor updates.', 'price_tzs' => 800000, 'price_usd' => 320],
        ];

        foreach ($plans as $plan) {
            CarePlan::updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['active' => true])
            );
        }
    }
}
