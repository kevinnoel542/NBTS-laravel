<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\BloodCenterDirectoryController;
use App\Http\Controllers\Web\CampaignDirectoryController;
use App\Http\Controllers\Web\EligibilityCheckerController;
use App\Http\Controllers\Web\AnalyticsController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Public Information
Route::get('/about', function () {
    return view('web.about');
})->name('about');

Route::get('/download-app', function () {
    return view('web.download');
})->name('download');

Route::get('/eligibility', [EligibilityCheckerController::class, 'index'])->name('eligibility');
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

// Public Directories
Route::get('/centers', [BloodCenterDirectoryController::class, 'index'])->name('centers.index');
Route::get('/centers/{center}', [BloodCenterDirectoryController::class, 'show'])->name('centers.show');
Route::get('/campaigns', [CampaignDirectoryController::class, 'index'])->name('campaigns.index');
Route::get('/campaigns/{campaign}', [CampaignDirectoryController::class, 'show'])->name('campaigns.show');
