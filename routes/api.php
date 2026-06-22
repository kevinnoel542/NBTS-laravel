<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BloodCenterController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\DonorCardController;
use App\Http\Controllers\Api\EligibilityController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\Staff\AppointmentManagementController;
use App\Http\Controllers\Api\Staff\DonationRecordingController;
use App\Http\Controllers\Api\Staff\DonorLookupController;
use App\Http\Controllers\Api\Staff\EligibilityManagementController;
use App\Http\Controllers\Api\Staff\InventoryController;
use App\Http\Controllers\Api\Staff\ReportController;



/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Connectivity Test
    Route::get('/ping', function () {
        return response()->json(['status' => 'API working']);
    });

    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    // Public mobile lookup routes
    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::get('/campaigns/{id}', [CampaignController::class, 'show']);
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);
    Route::get('/blood-centers', [BloodCenterController::class, 'index']);
    Route::get('/blood-centers/{id}', [BloodCenterController::class, 'show']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/user', function (Request $request) {
            return new \App\Http\Resources\UserResource($request->user()->load('donorProfile'));
        });

        // Profile Management
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::get('/donor-card', [DonorCardController::class, 'show']);
        Route::get('/eligibility', [EligibilityController::class, 'show']);
        Route::get('/loyalty', [LoyaltyController::class, 'me']);
        Route::get('/leaderboard', [LoyaltyController::class, 'leaderboard']);

        // Donation History
        Route::get('/donations', [DonationController::class, 'index']);
        Route::get('/donations/summary', [DonationController::class, 'summary']);

        // Appointments
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::get('/appointments/upcoming', [AppointmentController::class, 'upcoming']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
        Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
        Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
        Route::get('/blood-centers/{bloodCenter}/available-slots', [AppointmentController::class, 'availableSlots']);

        // Staff Operations
        Route::prefix('staff')->group(function () {
            Route::get('/donors/search', [DonorLookupController::class, 'index']);
            Route::get('/donors/{donor}', [DonorLookupController::class, 'show']);
            Route::post('/donors/{donor}/eligibility-check', [EligibilityManagementController::class, 'check']);
            Route::post('/donors/{donor}/deferrals', [EligibilityManagementController::class, 'defer']);
            Route::post('/deferrals/{deferral}/lift', [EligibilityManagementController::class, 'liftDeferral']);
            Route::post('/appointments/{appointment}/confirm', [AppointmentManagementController::class, 'confirm']);
            Route::post('/appointments/{appointment}/cancel', [AppointmentManagementController::class, 'cancel']);
            Route::post('/donations', [DonationRecordingController::class, 'store']);
            Route::post('/donations/{donation}/verify-blood-group', [DonationRecordingController::class, 'verifyBloodGroup']);
            Route::get('/inventory', [InventoryController::class, 'inventory']);
            Route::get('/blood-units', [InventoryController::class, 'units']);
            Route::post('/blood-units/{unit}/transition', [InventoryController::class, 'transitionUnit']);
            Route::post('/inventory/adjust', [InventoryController::class, 'adjust']);
            Route::post('/inventory/expire-due', [InventoryController::class, 'expireDue']);
            Route::get('/low-stock-alerts', [InventoryController::class, 'lowStockAlerts']);
            Route::post('/low-stock-alerts/{alert}/emergency-campaign', [InventoryController::class, 'createEmergencyCampaign']);
            Route::get('/reports/summary', [ReportController::class, 'summary']);
            Route::get('/reports/donations', [ReportController::class, 'donations']);
            Route::get('/reports/inventory', [ReportController::class, 'inventory']);
        });

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/notifications/register-token', [NotificationController::class, 'registerToken']);
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    });

});
