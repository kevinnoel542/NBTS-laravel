<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\BloodCenterDirectoryController;
use App\Http\Controllers\Web\CampaignDirectoryController;
use App\Http\Controllers\Web\EligibilityCheckerController;
use App\Http\Controllers\Web\AnalyticsController;
use App\Http\Controllers\Web\PublicPageController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Public Information
Route::get('/about', function () {
    return view('web.about');
})->name('about');

Route::get('/download-app', function () {
    return view('web.download');
})->name('download');

Route::get('/donate', [PublicPageController::class, 'donate'])->name('donate');
Route::get('/services', [PublicPageController::class, 'services'])->name('services');
Route::get('/news', [PublicPageController::class, 'news'])->name('news');
Route::get('/news/{article:slug}', [PublicPageController::class, 'newsShow'])->name('news.show');
Route::get('/publications', [PublicPageController::class, 'publications'])->name('publications');
Route::get('/faq', [PublicPageController::class, 'faq'])->name('faq');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
Route::get('/eligibility', [EligibilityCheckerController::class, 'index'])->name('eligibility');
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

// Public Directories
Route::get('/centers', [BloodCenterDirectoryController::class, 'index'])->name('centers.index');
Route::get('/centers/{center}', [BloodCenterDirectoryController::class, 'show'])->name('centers.show');
Route::get('/campaigns', [CampaignDirectoryController::class, 'index'])->name('campaigns.index');
Route::get('/campaigns/{campaign}', [CampaignDirectoryController::class, 'show'])->name('campaigns.show');
