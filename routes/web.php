<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Web\ArticleDirectoryController;
use App\Http\Controllers\Web\BloodCenterDirectoryController;
use App\Http\Controllers\Web\CampaignDirectoryController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ImpactController;
use App\Http\Controllers\Web\PublicPageController;
use App\Livewire\Operations\Overview;
use App\Livewire\Operations\Workspace;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::controller(PublicPageController::class)->group(function (): void {
    Route::get('about', 'about')->name('about');
    Route::get('contact', 'contact')->name('contact');
    Route::get('donate', 'donate')->name('donate');
    Route::get('download-app', 'download')->name('download');
    Route::get('eligibility', 'eligibility')->name('eligibility');
    Route::get('faq', 'faq')->name('faq');
    Route::get('services', 'services')->name('services');
});

Route::get('impact', ImpactController::class)->name('impact');

Route::controller(BloodCenterDirectoryController::class)
    ->prefix('centers')
    ->name('centers.')
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('{bloodCenter}', 'show')->name('show');
    });

Route::controller(CampaignDirectoryController::class)
    ->prefix('campaigns')
    ->name('campaigns.')
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('{campaign}', 'show')->name('show');
    });

Route::controller(ArticleDirectoryController::class)->group(function (): void {
    Route::get('news', 'index')->name('news.index');
    Route::get('news/{article:slug}', 'show')->name('news.show');
    Route::get('publications', 'publications')->name('publications');
});

Route::post('locale/{locale}', LocaleController::class)
    ->whereIn('locale', ['en', 'sw'])
    ->name('locale.update');

Route::middleware(['auth', 'active', 'staff', 'verified'])->group(function () {
    Route::livewire('dashboard', Overview::class)->name('dashboard');

    Route::livewire('operations/{workspace}', Workspace::class)
        ->whereIn('workspace', array_keys(config('operations.workspaces', [])))
        ->name('operations.workspace');
});

require __DIR__.'/settings.php';
