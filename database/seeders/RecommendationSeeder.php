<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recommendation;

class RecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $recommendations = [
            ['check_name' => 'Page Loads Within 3s on Mobile', 'area' => 'Speed', 'finding_example' => 'The page takes over 3 seconds to become usable on mobile', 'consequence' => 'Visitors may leave before the page becomes usable', 'solution' => 'Performance Optimization', 'service_type' => 'Web Development', 'priority' => 'high'],
            ['check_name' => 'No Broken Links', 'area' => 'Function', 'finding_example' => 'Some links on the page return 404 errors', 'consequence' => 'Visitors may reach dead pages and lose confidence', 'solution' => 'Website Maintenance', 'service_type' => 'Website Maintenance', 'priority' => 'high'],
            ['check_name' => 'Contact/Enquiry Form Exists', 'area' => 'Function', 'finding_example' => 'No contact or enquiry form found on the website', 'consequence' => 'Potential customers may struggle to enquire', 'solution' => 'Contact Integration', 'service_type' => 'Website Redesign', 'priority' => 'high'],
            ['check_name' => 'Privacy Policy Page', 'area' => 'Trust', 'finding_example' => 'No privacy policy page found on the website', 'consequence' => 'Reduced trust and possible compliance concerns', 'solution' => 'Policy Page Creation', 'service_type' => 'Website Improvement', 'priority' => 'medium'],
            ['check_name' => 'Responsive Layout', 'area' => 'Mobile', 'finding_example' => 'The page requires horizontal scrolling on mobile devices', 'consequence' => 'Mobile visitors may struggle to use the site', 'solution' => 'Responsive Redesign', 'service_type' => 'Responsive Web Development', 'priority' => 'high'],
            ['check_name' => 'Online Payment/Booking Path', 'area' => 'Commerce', 'finding_example' => 'No online payment or booking functionality detected', 'consequence' => 'Customers may have no direct digital conversion route', 'solution' => 'E-commerce Integration', 'service_type' => 'E-Commerce', 'priority' => 'medium'],
            ['check_name' => 'SSL Certificate Valid', 'area' => 'Security', 'finding_example' => 'Website does not use HTTPS or has an invalid certificate', 'consequence' => 'Visitors see security warnings and may leave immediately', 'solution' => 'SSL Setup & Configuration', 'service_type' => 'Website Maintenance', 'priority' => 'high'],
            ['check_name' => 'Total Page <2 MB', 'area' => 'Speed', 'finding_example' => 'The page weighs over 2MB, mostly from uncompressed images', 'consequence' => 'Slow loading on mobile networks, higher bounce rates', 'solution' => 'Image Optimization & Caching', 'service_type' => 'Performance Optimization', 'priority' => 'medium'],
        ];

        foreach ($recommendations as $rec) {
            Recommendation::updateOrCreate(
                ['check_name' => $rec['check_name']],
                array_merge($rec, ['active' => true])
            );
        }
    }
}
