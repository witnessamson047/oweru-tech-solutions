<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServicePackage;

class ServicePackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            // Individuals & Professionals
            ['name' => 'Starter Website', 'slug' => 'starter-website', 'group' => 'individuals', 'description' => 'A clean, professional single-page website perfect for freelancers and consultants. Includes contact form, social links, and mobile-responsive design.', 'price_tzs' => 500000, 'price_usd' => 200, 'delivery_days' => 7, 'sort_order' => 1],
            ['name' => 'Professional Portfolio', 'slug' => 'professional-portfolio', 'group' => 'individuals', 'description' => 'Multi-page portfolio website with project showcase, testimonials, blog section, and advanced contact features. Ideal for established professionals.', 'price_tzs' => 1200000, 'price_usd' => 480, 'delivery_days' => 14, 'is_featured' => true, 'sort_order' => 2],

            // SMEs
            ['name' => 'Business Website', 'slug' => 'business-website', 'group' => 'sme', 'description' => 'Complete business website with up to 10 pages, service listings, team profiles, Google Maps integration, and enquiry management system.', 'price_tzs' => 3000000, 'price_usd' => 1200, 'delivery_days' => 21, 'sort_order' => 1],
            ['name' => 'E-Commerce Starter', 'slug' => 'ecommerce-starter', 'group' => 'sme', 'description' => 'Online store with product catalogue, shopping cart, secure checkout (mobile money + card), inventory management, and order notifications.', 'price_tzs' => 5000000, 'price_usd' => 2000, 'delivery_days' => 30, 'is_featured' => true, 'sort_order' => 2],

            // Business & Corporate
            ['name' => 'Corporate Platform', 'slug' => 'corporate-platform', 'group' => 'corporate', 'description' => 'Enterprise-grade website with CMS, multi-language support, custom integrations, analytics dashboard, and priority support.', 'price_tzs' => 10000000, 'price_usd' => 4000, 'delivery_days' => 45, 'sort_order' => 1],
            ['name' => 'Digital Transformation Suite', 'slug' => 'digital-transformation', 'group' => 'corporate', 'description' => 'Complete digital transformation: website, internal tools, API integrations, automation workflows, staff training, and 6-month support.', 'price_tzs' => 20000000, 'price_usd' => 8000, 'delivery_days' => 60, 'is_featured' => true, 'sort_order' => 2],
        ];

        foreach ($packages as $package) {
            ServicePackage::updateOrCreate(
                ['slug' => $package['slug']],
                array_merge($package, ['active' => true])
            );
        }
    }
}
