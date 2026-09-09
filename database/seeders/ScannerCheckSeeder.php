<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScannerCheck;

class ScannerCheckSeeder extends Seeder
{
    public function run(): void
    {
        $checks = [
            // Security - 20 points
            ['name' => 'SSL Certificate Valid', 'slug' => 'ssl-valid', 'area' => 'Security', 'weight' => 10, 'description' => 'Site loads over HTTPS with a valid SSL certificate'],
            ['name' => 'SSL Certificate >30 Days to Expiry', 'slug' => 'ssl-expiry', 'area' => 'Security', 'weight' => 5, 'description' => 'SSL certificate has more than 30 days before expiry'],
            ['name' => 'No Insecure Items on Secure Page', 'slug' => 'no-mixed-content', 'area' => 'Security', 'weight' => 5, 'description' => 'No HTTP resources loaded on HTTPS pages'],

            // Mobile - 20 points
            ['name' => 'Responsive Layout', 'slug' => 'responsive-layout', 'area' => 'Mobile', 'weight' => 8, 'description' => 'Page renders properly at phone width without horizontal scrolling'],
            ['name' => 'Readable Body Text', 'slug' => 'readable-text', 'area' => 'Mobile', 'weight' => 6, 'description' => 'Body text is at least 14px on mobile'],
            ['name' => 'Tap-Friendly Buttons/Links', 'slug' => 'tap-friendly', 'area' => 'Mobile', 'weight' => 6, 'description' => 'Interactive elements are at least 44x44px tap targets'],

            // Speed - 15 points
            ['name' => 'Page Loads Within 3s on Mobile', 'slug' => 'load-time', 'area' => 'Speed', 'weight' => 5, 'description' => 'Page becomes usable within 3 seconds on simulated mobile connection'],
            ['name' => 'Total Page <2 MB', 'slug' => 'page-size', 'area' => 'Speed', 'weight' => 5, 'description' => 'Total page weight is under 2 megabytes'],
            ['name' => 'Images Compressed/Sized', 'slug' => 'image-optimization', 'area' => 'Speed', 'weight' => 5, 'description' => 'Images are compressed and appropriately sized'],

            // Function - 15 points
            ['name' => 'No Broken Links', 'slug' => 'no-broken-links', 'area' => 'Function', 'weight' => 5, 'description' => 'All links on the page resolve successfully'],
            ['name' => 'Contact/Enquiry Form Exists', 'slug' => 'contact-form', 'area' => 'Function', 'weight' => 5, 'description' => 'A contact or enquiry form is present and functional'],
            ['name' => 'Phone/Email Are Tappable', 'slug' => 'tappable-contact', 'area' => 'Function', 'weight' => 5, 'description' => 'Phone numbers and email addresses are clickable links'],

            // Findability - 12 points
            ['name' => 'Unique Page Title', 'slug' => 'page-title', 'area' => 'Findability', 'weight' => 3, 'description' => 'Page has a unique, descriptive title tag'],
            ['name' => 'Meta Description Present', 'slug' => 'meta-description', 'area' => 'Findability', 'weight' => 3, 'description' => 'Page has a meta description tag'],
            ['name' => 'Appears for Business Name Search', 'slug' => 'business-name-search', 'area' => 'Findability', 'weight' => 3, 'description' => 'Website appears in search results for its own business name'],
            ['name' => 'Google Business Profile Points to Site', 'slug' => 'gbp-link', 'area' => 'Findability', 'weight' => 3, 'description' => 'Google Business Profile listing links to this website'],

            // Trust - 10 points
            ['name' => 'Registered Company Name', 'slug' => 'company-name', 'area' => 'Trust', 'weight' => 3, 'description' => 'Registered business/company name is visible on the site'],
            ['name' => 'Physical Address Listed', 'slug' => 'physical-address', 'area' => 'Trust', 'weight' => 3, 'description' => 'A physical business address is provided'],
            ['name' => 'Privacy Policy Page', 'slug' => 'privacy-policy', 'area' => 'Trust', 'weight' => 2, 'description' => 'A privacy policy page exists and is accessible'],
            ['name' => 'Terms of Service Page', 'slug' => 'terms-of-service', 'area' => 'Trust', 'weight' => 2, 'description' => 'A terms of service page exists and is accessible'],

            // Commerce - 5 points
            ['name' => 'Online Payment/Booking Path', 'slug' => 'payment-path', 'area' => 'Commerce', 'weight' => 5, 'description' => 'An online payment or booking functionality exists'],

            // Freshness - 3 points
            ['name' => 'Content Changed Within 12 Months', 'slug' => 'content-freshness', 'area' => 'Freshness', 'weight' => 2, 'description' => 'Website content has been updated within the last 12 months'],
            ['name' => 'Current Copyright Year', 'slug' => 'copyright-year', 'area' => 'Freshness', 'weight' => 1, 'description' => 'Footer copyright year matches the current year'],
        ];

        foreach ($checks as $check) {
            ScannerCheck::updateOrCreate(
                ['slug' => $check['slug']],
                $check
            );
        }
    }
}
