<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServicePackage;
use App\Models\ServiceLine;

class AdditionalPackagesSeeder extends Seeder
{
    /**
     * Packages covering the CRM Solutions and Network service lines
     * so every service line has sellable packages.
     */
    public function run(): void
    {
        $crm = ServiceLine::where('slug', 'crm-solutions')->first();
        $network = ServiceLine::where('slug', 'network-design')->first();

        $packages = [
            [
                'name' => 'CRM Starter',
                'slug' => 'crm-starter',
                'group' => 'sme',
                'service_line_id' => $crm?->id,
                'description' => 'Lead capture from your website and scanner, a visual sales pipeline, automated follow-up reminders, and basic reporting. Perfect for small teams getting organised.',
                'price_tzs' => 4500000,
                'price_usd' => 1800,
                'delivery_days' => 21,
                'is_featured' => true,
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'CRM Enterprise',
                'slug' => 'crm-enterprise',
                'group' => 'corporate',
                'service_line_id' => $crm?->id,
                'description' => 'Everything in CRM Starter plus multi-department pipelines, role-based access, API integrations (accounting, email, WhatsApp), custom dashboards and staff training.',
                'price_tzs' => 15000000,
                'price_usd' => 6000,
                'delivery_days' => 45,
                'is_featured' => false,
                'active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Office Network Setup',
                'slug' => 'office-network-setup',
                'group' => 'sme',
                'service_line_id' => $network?->id,
                'description' => 'Site survey, structured cabling plan, router/switch configuration, secure Wi-Fi with guest isolation, and printer/file sharing setup for up to 25 devices.',
                'price_tzs' => 2500000,
                'price_usd' => 1000,
                'delivery_days' => 14,
                'is_featured' => false,
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Enterprise Network & Maintenance',
                'slug' => 'enterprise-network',
                'group' => 'corporate',
                'service_line_id' => $network?->id,
                'description' => 'Multi-floor network design, VLAN segmentation, redundant links, VPN for remote staff, plus a 12-month maintenance contract with quarterly health checks.',
                'price_tzs' => 12000000,
                'price_usd' => 4800,
                'delivery_days' => 60,
                'is_featured' => false,
                'active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($packages as $package) {
            ServicePackage::updateOrCreate(
                ['slug' => $package['slug']],
                $package
            );
        }
    }
}
