<?php

namespace App\Services;

use App\Models\FCMToken;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notifyUser(User $user, string $title, string $body, string $type = 'general', array $data = [], ?string $actionUrl = null): UserNotification
    {
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);

        if ($this->shouldSendPush($user)) {
            $sent = $this->sendPushToUser($user, $title, $body, $data + [
                'notification_id' => (string) $notification->id,
                'type' => $type,
            ]);

            if ($sent) {
                $notification->update(['sent_at' => now()]);
            }
        }

        return $notification;
    }

    public function notifyDonors(string $title, string $body, string $type = 'general', array $data = [], ?string $actionUrl = null): void
    {
        User::role('donor')
            ->where('is_active', true)
            ->with('donorProfile')
            ->chunkById(100, function ($users) use ($title, $body, $type, $data, $actionUrl): void {
                foreach ($users as $user) {
                    $this->notifyUser($user, $title, $body, $type, $data, $actionUrl);
                }
            });
    }

    private function shouldSendPush(User $user): bool
    {
        $user->loadMissing('donorProfile');

        if ($user->donorProfile && ! $user->donorProfile->push_notifications_enabled) {
            return false;
        }

        return config('services.firebase.enabled') && FCMToken::where('user_id', $user->id)->exists();
    }

    private function sendPushToUser(User $user, string $title, string $body, array $data = []): bool
    {
        $projectId = config('services.firebase.project_id');
        $accessToken = $this->firebaseAccessToken();

        if (! $projectId || ! $accessToken) {
            return false;
        }

        $tokens = FCMToken::where('user_id', $user->id)->pluck('token');

        if ($tokens->isEmpty()) {
            return false;
        }

        $success = false;

        foreach ($tokens as $token) {
            try {
                $response = Http::withToken($accessToken)
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'data' => array_map('strval', $data),
                        ],
                    ]);

                if ($response->successful()) {
                    $success = true;
                } else {
                    Log::warning('Firebase push notification was rejected.', [
                        'user_id' => $user->id,
                        'status' => $response->status(),
                        'body' => $response->json(),
                    ]);
                }
            } catch (\Throwable $exception) {
                Log::warning('Firebase push notification failed.', [
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $success;
    }

    private function firebaseAccessToken(): ?string
    {
        return Cache::remember('firebase.messaging.access_token', now()->addMinutes(50), function (): ?string {
            $credentials = $this->firebaseCredentials();

            if (! $credentials) {
                return null;
            }

            $now = time();
            $header = $this->base64UrlEncode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ], JSON_THROW_ON_ERROR));
            $claim = $this->base64UrlEncode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ], JSON_THROW_ON_ERROR));
            $unsignedJwt = $header . '.' . $claim;

            openssl_sign($unsignedJwt, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsignedJwt . '.' . $this->base64UrlEncode($signature),
            ]);

            if (! $response->successful()) {
                Log::warning('Firebase access token request failed.', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return null;
            }

            return $response->json('access_token');
        });
    }

    private function firebaseCredentials(): ?array
    {
        $path = config('services.firebase.credentials');

        if (! $path) {
            return null;
        }

        $fullPath = str_starts_with($path, '/') ? $path : base_path($path);

        if (! is_file($fullPath)) {
            Log::warning('Firebase credentials file was not found.', ['path' => $fullPath]);

            return null;
        }

        try {
            $credentials = json_decode(file_get_contents($fullPath), true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            Log::warning('Firebase credentials file could not be read.', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (empty($credentials['client_email']) || empty($credentials['private_key'])) {
            Log::warning('Firebase credentials file is missing required fields.');

            return null;
        }

        return $credentials;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
