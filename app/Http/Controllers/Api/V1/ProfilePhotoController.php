<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Profile\PrepareMobileUserResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfilePhotoRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final class ProfilePhotoController extends Controller
{
    public function __invoke(
        UpdateProfilePhotoRequest $request,
        AuditLogger $auditLogger,
        PrepareMobileUserResource $prepareMobileUserResource,
    ): UserResource {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $path = $request->photo()->store('profile-photos/'.$user->id, 'public');

        if (! is_string($path)) {
            throw new RuntimeException('The profile photo could not be stored.');
        }

        $oldPath = $user->profile_photo_path;

        try {
            DB::transaction(function () use ($user, $path, $auditLogger): void {
                $user->forceFill(['profile_photo_path' => $path])->save();
                $auditLogger->record(
                    actor: $user,
                    action: 'mobile.profile_photo_updated',
                    subject: $user,
                );
            }, attempts: 3);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        if (is_string($oldPath)
            && $oldPath !== ''
            && ! str_starts_with($oldPath, 'http://')
            && ! str_starts_with($oldPath, 'https://')) {
            Storage::disk('public')->delete($oldPath);
        }

        return new UserResource($prepareMobileUserResource->handle($user));
    }
}
