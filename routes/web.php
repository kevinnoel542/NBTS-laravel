<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::post('locale/{locale}', LocaleController::class)
    ->whereIn('locale', ['en', 'sw'])
    ->name('locale.update');

Route::middleware(['auth', 'active', 'staff', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
