<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScannerApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Scanner API endpoints (called by JavaScript frontend)
Route::post('/scanner/scan', [ScannerApiController::class, 'scan'])->name('api.scanner.scan');
Route::get('/scanner/scans/{id}', [ScannerApiController::class, 'getStatus'])->name('api.scanner.status');
Route::post('/scanner/callback', [ScannerApiController::class, 'callback'])->name('api.scanner.callback');
