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
});

