<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\BloodCenterController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\CurrentUserController;
use App\Http\Controllers\Api\V1\DonationController;
use App\Http\Controllers\Api\V1\DonationScheduleController;
use App\Http\Controllers\Api\V1\DonorCardController;
use App\Http\Controllers\Api\V1\EligibilityController;
use App\Http\Controllers\Api\V1\FirebaseAuthenticationController;
use App\Http\Controllers\Api\V1\LogoutController;
use App\Http\Controllers\Api\V1\MobileLoginController;
use App\Http\Controllers\Api\V1\MobileRegistrationController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProfilePhotoController;
use App\Http\Controllers\Api\V1\PublicationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
    Route::get('/publications', [PublicationController::class, 'index'])->name('publications.index');
    Route::get('/publications/{article}', [PublicationController::class, 'show'])->name('publications.show');
    Route::get('/schedules', [DonationScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/{campaign}', [DonationScheduleController::class, 'show'])->name('schedules.show');

    Route::get('/blood-centers', [BloodCenterController::class, 'index'])->name('blood-centers.index');
    Route::get('/blood-centers/{bloodCenter}', [BloodCenterController::class, 'show'])->name('blood-centers.show');
    Route::get('/blood-centers/{bloodCenter}/available-slots', [AppointmentController::class, 'availableSlots'])
        ->name('blood-centers.available-slots');

    Route::post('/auth/register', MobileRegistrationController::class)
        ->middleware('throttle:5,1')
        ->name('auth.register');
    Route::post('/auth/login', MobileLoginController::class)
        ->middleware('throttle:5,1')
        ->name('auth.login');
    Route::post('/auth/firebase', FirebaseAuthenticationController::class)
        ->middleware('throttle:10,1')
        ->name('auth.firebase');

    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->middleware('abilities:donor:read')
            ->name('notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
            ->middleware('abilities:donor:read')
            ->name('notifications.unread-count');
        Route::post('/notifications/register-token', [NotificationController::class, 'registerToken'])
            ->middleware('abilities:donor:write')
            ->name('notifications.register-token');
        Route::delete('/notifications/device-token', [NotificationController::class, 'unregisterToken'])
            ->middleware('abilities:donor:write')
            ->name('notifications.unregister-token');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->middleware('abilities:donor:write')
            ->name('notifications.mark-all-read');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->middleware('abilities:donor:write')
            ->name('notifications.read');
        Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
            ->middleware('abilities:donor:write')
            ->name('notifications.destroy');

        Route::get('/donor-card', DonorCardController::class)
            ->middleware('abilities:donor:read')
            ->name('donor-card.show');
        Route::get('/eligibility', EligibilityController::class)
            ->middleware('abilities:donor:read')
            ->name('eligibility.show');
        Route::get('/donations', [DonationController::class, 'index'])
            ->middleware('abilities:donor:read')
            ->name('donations.index');
        Route::get('/donations/summary', [DonationController::class, 'summary'])
            ->middleware('abilities:donor:read')
            ->name('donations.summary');

        Route::get('/appointments', [AppointmentController::class, 'index'])
            ->middleware('abilities:donor:read')
            ->name('appointments.index');
        Route::get('/appointments/upcoming', [AppointmentController::class, 'upcoming'])
            ->middleware('abilities:donor:read')
            ->name('appointments.upcoming');
        Route::post('/appointments', [AppointmentController::class, 'store'])
            ->middleware('abilities:donor:write')
            ->name('appointments.store');
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])
            ->middleware('abilities:donor:read')
            ->name('appointments.show');
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])
            ->middleware('abilities:donor:write')
            ->name('appointments.update');
        Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
            ->middleware('abilities:donor:write')
            ->name('appointments.cancel');

        Route::get('/me', CurrentUserController::class)
            ->middleware('abilities:donor:read')
            ->name('me');
        Route::get('/user', CurrentUserController::class)
            ->middleware('abilities:donor:read')
            ->name('user');
        Route::get('/profile', [ProfileController::class, 'show'])
            ->middleware('abilities:donor:read')
            ->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])
            ->middleware('abilities:donor:write')
            ->name('profile.update');
        Route::post('/profile/photo', ProfilePhotoController::class)
            ->middleware('abilities:donor:write')
            ->name('profile.photo');
        Route::post('/logout', LogoutController::class)->name('logout');
        Route::post('/auth/logout', LogoutController::class)->name('auth.logout');
    });
});
