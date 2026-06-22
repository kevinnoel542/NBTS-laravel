<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\FCMToken;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Register a new FCM device token.
     */
    public function registerToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|unique:f_c_m_tokens,token',
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
