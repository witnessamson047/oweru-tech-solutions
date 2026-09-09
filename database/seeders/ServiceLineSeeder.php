<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceLineSeeder extends Seeder
{
    /**
     * The four service lines Oweru offers (per the build brief).
     */
    public const LINES = [
        ['name' => 'Software Development', 'slug' => 'software-development', 'icon' => '⚙️', 'sort_order' => 1, 'description' => 'Custom business systems, internal tools, APIs and automation workflows built for the way you work.'],
        ['name' => 'Mobile & Web Design & Development', 'slug' => 'mobile-web-development', 'icon' => '🌐', 'sort_order' => 2, 'description' => 'Responsive, fast and beautiful websites and web apps — from single-page sites to e-commerce platforms.'],
        ['name' => 'CRM Solutions', 'slug' => 'crm-solutions', 'icon' => '📈', 'sort_order' => 3, 'description' => 'Customer relationship systems that capture every lead, track the pipeline and automate follow-ups.'],
        ['name' => 'Network Design & Maintenance', 'slug' => 'network-design', 'icon' => '🛰️', 'sort_order' => 4, 'description' => 'Office network design, structured cabling, Wi-Fi coverage and ongoing maintenance contracts.'],
    ];

    public function run(): void
    {
        foreach (self::LINES as $line) {
            DB::table('service_lines')->updateOrInsert(
                ['slug' => $line['slug']],
                array_merge($line, ['active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // Attach packages to their closest service line so nothing is orphaned.
        $map = [
            'starter-website' => 'mobile-web-development',
            'professional-portfolio' => 'mobile-web-development',
            'business-website' => 'mobile-web-development',
            'ecommerce-starter' => 'mobile-web-development',
            'corporate-platform' => 'mobile-web-development',
            'digital-transformation' => 'software-development',
        ];

        foreach ($map as $packageSlug => $lineSlug) {
            DB::table('service_packages')->where('slug', $packageSlug)
                ->update(['service_line_id' => DB::table('service_lines')->where('slug', $lineSlug)->value('id')]);
        }
    }
}
