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

            // Full mapping (2026-09-24): every scanner check has a service to sell.
            ['check_name' => 'SSL Certificate >30 Days to Expiry', 'area' => 'Security', 'finding_example' => 'The SSL certificate expires within 30 days', 'consequence' => 'The site can suddenly show security warnings when the certificate lapses', 'solution' => 'SSL Setup & Configuration', 'service_type' => 'Website Maintenance', 'priority' => 'medium'],
            ['check_name' => 'No Insecure Items on Secure Page', 'area' => 'Security', 'finding_example' => 'Insecure (HTTP) resources are loaded on the secure page', 'consequence' => 'Browsers flag the site as not fully secure, eroding visitor trust', 'solution' => 'Security Hardening', 'service_type' => 'Website Maintenance', 'priority' => 'high'],
            ['check_name' => 'Readable Body Text', 'area' => 'Mobile', 'finding_example' => 'Body text is too small to read comfortably on a phone', 'consequence' => 'Mobile visitors pinch-zoom or give up reading', 'solution' => 'Mobile Typography & Layout Pass', 'service_type' => 'Responsive Web Development', 'priority' => 'medium'],
            ['check_name' => 'Tap-Friendly Buttons/Links', 'area' => 'Mobile', 'finding_example' => 'Buttons and links are too small or too close together for fingers', 'consequence' => 'Mobile visitors tap the wrong thing or miss calls-to-action entirely', 'solution' => 'Mobile UX Overhaul', 'service_type' => 'Responsive Web Development', 'priority' => 'high'],
            ['check_name' => 'Images Compressed/Sized', 'area' => 'Speed', 'finding_example' => 'Images are not compressed or sized for the space they occupy', 'consequence' => 'The page crawls on mobile data and visitors leave before it loads', 'solution' => 'Image Optimization & Caching', 'service_type' => 'Performance Optimization', 'priority' => 'medium'],
            ['check_name' => 'Phone/Email Are Tappable', 'area' => 'Function', 'finding_example' => 'Phone numbers and email addresses are plain text, not tappable links', 'consequence' => 'Interested visitors cannot reach the business with one tap', 'solution' => 'Contact Integration', 'service_type' => 'Website Redesign', 'priority' => 'high'],
            ['check_name' => 'Unique Page Title', 'area' => 'Findability', 'finding_example' => 'Pages are missing unique, descriptive titles', 'consequence' => 'Search engines and prospects cannot tell what the business does', 'solution' => 'SEO Foundations', 'service_type' => 'Website Improvement', 'priority' => 'high'],
            ['check_name' => 'Meta Description Present', 'area' => 'Findability', 'finding_example' => 'No meta description — search results show uncontrolled snippets', 'consequence' => 'Search listings look unprofessional and earn fewer clicks', 'solution' => 'SEO Foundations', 'service_type' => 'Website Improvement', 'priority' => 'medium'],
            ['check_name' => 'Appears for Business Name Search', 'area' => 'Findability', 'finding_example' => 'The site does not appear when searching the exact business name', 'consequence' => 'Prospects who hear about the business cannot find it online', 'solution' => 'SEO Foundations', 'service_type' => 'Website Improvement', 'priority' => 'medium'],
            ['check_name' => 'Google Business Profile Points to Site', 'area' => 'Findability', 'finding_example' => 'No Google Business Profile, or it does not link to the website', 'consequence' => 'The business is invisible on Maps and local search', 'solution' => 'Local SEO & Google Business Profile Setup', 'service_type' => 'Website Improvement', 'priority' => 'medium'],
            ['check_name' => 'Registered Company Name', 'area' => 'Trust', 'finding_example' => 'The registered company name is not displayed on the site', 'consequence' => 'Visitors cannot verify they are dealing with a real company', 'solution' => 'Trust Pack (company details display)', 'service_type' => 'Website Improvement', 'priority' => 'medium'],
            ['check_name' => 'Physical Address Listed', 'area' => 'Trust', 'finding_example' => 'No physical address visible anywhere on the site', 'consequence' => 'Visitors may doubt the business is legitimate', 'solution' => 'Trust Pack (address & contact display)', 'service_type' => 'Website Improvement', 'priority' => 'medium'],
            ['check_name' => 'Terms of Service Page', 'area' => 'Trust', 'finding_example' => 'No terms of service page found on the website', 'consequence' => 'No clear rules for transactions raises disputes and looks unprofessional', 'solution' => 'Policy Page Creation', 'service_type' => 'Website Improvement', 'priority' => 'low'],
            ['check_name' => 'Content Changed Within 12 Months', 'area' => 'Freshness', 'finding_example' => 'No content changes in over 12 months', 'consequence' => 'The site looks abandoned — visitors question whether the business is active', 'solution' => 'Website Maintenance Plan', 'service_type' => 'Website Maintenance', 'priority' => 'medium'],
            ['check_name' => 'Current Copyright Year', 'area' => 'Freshness', 'finding_example' => 'The footer copyright year is outdated', 'consequence' => 'A small detail that signals the whole site is unmaintained', 'solution' => 'Website Maintenance Plan', 'service_type' => 'Website Maintenance', 'priority' => 'low'],
        ];

        foreach ($recommendations as $rec) {
            Recommendation::updateOrCreate(
                ['check_name' => $rec['check_name']],
                array_merge($rec, ['active' => true])
            );
        }
    }
}
