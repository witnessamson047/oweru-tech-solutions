<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\CarePlanController;
use App\Http\Controllers\Admin\EnquiryController as AdminEnquiryController;
use App\Http\Controllers\Admin\PipelineController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\Admin\ScanController;
use App\Http\Controllers\Admin\RecommendationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ScannerCheckController;
use App\Http\Controllers\Admin\ScrapedBusinessController;
use App\Http\Controllers\Admin\ScrapeTargetController;
use App\Http\Controllers\Admin\DiscoveryController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Debug routes for troubleshooting
Route::get('/debug/db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'connected',
            'database' => Config::get('database.default'),
            'host' => env('DB_HOST'),
            'mailer' => Config::get('mail.default'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'database' => Config::get('database.default'),
        ], 500);
    }
});

Route::get('/debug/session', function () {
    return response()->json([
        'session_driver' => Config::get('session.driver'),
        'session_lifetime' => Config::get('session.lifetime'),
    ]);
});

// Build 1 - Service Packages
Route::get('/services', [PackageController::class, 'index'])->name('packages.index');
Route::get('/services/{package:slug}', [PackageController::class, 'show'])->name('packages.show');

// Build 2 - Enquiry Form
Route::get('/enquiry', [EnquiryController::class, 'create'])->name('enquiry.create');
Route::post('/enquiry', [EnquiryController::class, 'store'])->name('enquiry.store');

// Build 3 - Public Scanner
Route::get('/website-check', [ScannerController::class, 'index'])->name('scanner.index');
Route::post('/scanner/report-request', [ScannerController::class, 'reportRequest'])->name('scanner.report-request');

// Payments (PesaPal)
Route::get('/pay/{type}/{id}', [PaymentController::class, 'checkout'])->name('payment.checkout');
Route::post('/pay', [PaymentController::class, 'initiate'])->name('payment.initiate');
Route::get('/pay/status/{ref}', [PaymentController::class, 'status'])->name('payment.status');
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::get('/payment/ipn', [PaymentController::class, 'ipn'])->name('payment.ipn');

// Public receipt download (token-guarded — owner link from emails / status page)
Route::get('/receipt/{invoice}/{token}', [PaymentController::class, 'downloadReceipt'])
    ->name('invoice.receipt')->middleware('throttle:30,1');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Build 1 - Service Packages Management
    Route::resource('packages', AdminPackageController::class);

    // Build 1 - Care Plans Management
    Route::resource('care-plans', CarePlanController::class)->except(['show']);

    // Build 2 - Enquiries Management
    Route::get('enquiries', [AdminEnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('enquiries/{enquiry}', [AdminEnquiryController::class, 'show'])->name('enquiries.show');
    Route::patch('enquiries/{enquiry}/stage', [AdminEnquiryController::class, 'updateStage'])->name('enquiries.stage');
    Route::patch('enquiries/{enquiry}/owner', [AdminEnquiryController::class, 'updateOwner'])->name('enquiries.owner');
    Route::post('enquiries/{enquiry}/notes', [AdminEnquiryController::class, 'updateNotes'])->name('enquiries.notes');

    // Build 2 - Pipeline View
    Route::get('pipeline', [PipelineController::class, 'index'])->name('pipeline.index');

    // Build 3 - Websites Management
    Route::resource('websites', WebsiteController::class);
    Route::patch('websites/{website}/exclude', [WebsiteController::class, 'exclude'])->name('websites.exclude');

    // Build 3 - Scans Management
    Route::get('scans', [ScanController::class, 'index'])->name('scans.index');
    Route::get('scans/{scan}', [ScanController::class, 'show'])->name('scans.show');
    Route::post('websites/{website}/run-scan', [ScanController::class, 'run'])->name('scans.run');
    Route::post('websites/run-batch-scan', [ScanController::class, 'runBatch'])->name('scans.runBatch');

    // Recommendations Engine
    Route::resource('recommendations', RecommendationController::class)->except(['show']);

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
    Route::get('reports/{scan}/generate', [ReportController::class, 'generate'])->name('reports.generate');

    // Scanner Checks Management
    Route::resource('scanner-checks', ScannerCheckController::class)->except(['show']);

    // Payments
    Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');

    // Invoices — 50% deposit invoicing with automatic receipts
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue'])->name('invoices.issue');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])->name('invoices.receipt');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');

    // Scraper V1 — public business info scraping
    Route::get('scraped-businesses', [ScrapedBusinessController::class, 'index'])->name('scraped-businesses.index');
    Route::get('scraped-businesses/export', [ScrapedBusinessController::class, 'export'])->name('scraped-businesses.export');
    Route::post('scraped-businesses', [ScrapedBusinessController::class, 'store'])->name('scraped-businesses.store');
    Route::get('scraped-businesses/{scrapedBusiness}', [ScrapedBusinessController::class, 'show'])->name('scraped-businesses.show');
    Route::post('scraped-businesses/{scrapedBusiness}/run-health-scan', [ScrapedBusinessController::class, 'runHealthScan'])->name('scraped-businesses.run-health-scan');
    Route::post('scraped-businesses/{scrapedBusiness}/rescrape', [ScrapedBusinessController::class, 'rescrape'])->name('scraped-businesses.rescrape');
    Route::post('scraped-businesses/{scrapedBusiness}/add-to-leads', [ScrapedBusinessController::class, 'addToLeads'])->name('scraped-businesses.add-to-leads');

    // Website Discovery — OSM/Overpass city+category search feeding the queue
    Route::get('discovery', [DiscoveryController::class, 'index'])->name('discovery.index');
    Route::post('discovery', [DiscoveryController::class, 'store'])->name('discovery.store');
    Route::get('discovery/leads', [DiscoveryController::class, 'leads'])->name('discovery.leads');
    Route::post('discovery/leads/{lead}/contacted', [DiscoveryController::class, 'markContacted'])->name('discovery.leads.contacted');

    // Scraper V2 — 24/7 auto-scraper queue
    Route::get('scrape-targets', [ScrapeTargetController::class, 'index'])->name('scrape-targets.index');
    Route::post('scrape-targets', [ScrapeTargetController::class, 'store'])->name('scrape-targets.store');
    Route::post('scrape-targets/{target}/pause', [ScrapeTargetController::class, 'pause'])->name('scrape-targets.pause');
    Route::post('scrape-targets/{target}/resume', [ScrapeTargetController::class, 'resume'])->name('scrape-targets.resume');
    Route::post('scrape-targets/{target}/run-now', [ScrapeTargetController::class, 'runNow'])->name('scrape-targets.run-now');
    Route::post('scrape-targets/check-all', [ScrapeTargetController::class, 'checkAll'])->name('scrape-targets.check-all');
    Route::delete('scrape-targets/{target}', [ScrapeTargetController::class, 'destroy'])->name('scrape-targets.destroy');

});

