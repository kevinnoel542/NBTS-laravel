<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Notifications\RegisterFcmToken;
use App\Actions\Notifications\UnregisterFcmToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\NotificationIndexRequest;
use App\Http\Requests\Api\V1\RegisterFcmTokenRequest;
use App\Http\Requests\Api\V1\UnregisterFcmTokenRequest;
use App\Http\Resources\Api\V1\UserNotificationResource;
use App\Models\User;
use App\Models\UserNotification;
use App\RoleName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class NotificationController extends Controller
{
    public function index(NotificationIndexRequest $request): AnonymousResourceCollection
    {
        $user = $this->user($request);
        $query = $user->userNotifications()->latest('created_at')->latest('id');

        if (($unread = $request->unread()) !== null) {
            $unread ? $query->whereNull('read_at') : $query->whereNotNull('read_at');
        }

        if (($type = $request->notificationType()) !== null) {
            $query->where('type', $type);
        }

        return UserNotificationResource::collection(
            $query->paginate($request->perPage())->withQueryString(),
        )->additional([
            'meta' => [
                'unread_count' => $user->userNotifications()->whereNull('read_at')->count(),
            ],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $this->user($request);

        return response()->json([
            'data' => [
                'unread_count' => $user->userNotifications()->whereNull('read_at')->count(),
            ],
        ]);
    }

    public function markAsRead(Request $request, UserNotification $notification): JsonResponse
    {
        $user = $this->user($request);
        abort_unless($notification->user_id === $user->id, 404);
        $notification->markAsRead();

        return response()->json([
            'data' => (new UserNotificationResource($notification->refresh()))->resolve($request),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $updatedCount = $user->userNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'data' => [
                'unread_count' => 0,
                'updated_count' => $updatedCount,
            ],
        ]);
    }

    public function destroy(Request $request, UserNotification $notification): JsonResponse
    {
        $user = $this->user($request);
        abort_unless($notification->user_id === $user->id, 404);
        $notificationId = $notification->id;
        $notification->delete();

        return response()->json([
            'data' => [
                'id' => $notificationId,
                'unread_count' => $user->userNotifications()->whereNull('read_at')->count(),
            ],
        ]);
    }

    public function registerToken(
        RegisterFcmTokenRequest $request,
        RegisterFcmToken $registerFcmToken,
    ): JsonResponse {
        $fcmToken = $registerFcmToken->handle(
            user: $this->user($request),
            token: $request->fcmToken(),
            devicePlatform: $request->devicePlatform(),
        );

        return response()->json([
            'data' => [
                'id' => $fcmToken->id,
                'device_type' => $fcmToken->device_type->value,
                'registered' => true,
            ],
        ]);
    }

    public function unregisterToken(
        UnregisterFcmTokenRequest $request,
        UnregisterFcmToken $unregisterFcmToken,
    ): JsonResponse {
        $unregisterFcmToken->handle($this->user($request), $request->fcmToken());

        return response()->json(status: 204);
    }

    private function user(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        abort_unless($user->hasRole(RoleName::Donor->value), 403);

        return $user;
    }
}
