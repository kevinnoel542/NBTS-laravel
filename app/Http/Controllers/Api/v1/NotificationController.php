<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserNotificationResource;
use App\Models\FCMToken;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->userNotifications()
            ->latest()
            ->paginate((int) $request->integer('per_page', 20));

        return UserNotificationResource::collection($notifications)
            ->additional([
                'meta' => [
                    'unread_count' => $request->user()->userNotifications()->whereNull('read_at')->count(),
                ],
            ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'data' => [
                'unread_count' => $request->user()->userNotifications()->whereNull('read_at')->count(),
            ],
        ]);
    }

    public function markAsRead(Request $request, UserNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        $notification->markAsRead();

        return new UserNotificationResource($notification->refresh());
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->userNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Notifications marked as read',
            'data' => [
                'unread_count' => 0,
            ],
        ]);
    }

    /**
     * Register a new FCM device token.
     */
    public function registerToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios',
        ]);

        $fcmToken = FCMToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $request->user()?->id,
                'device_type' => $request->device_type ?? 'android',
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Device token registered successfully',
            'data' => $fcmToken
        ]);
    }
}
