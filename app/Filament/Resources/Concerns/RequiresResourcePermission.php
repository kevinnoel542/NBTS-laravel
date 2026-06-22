<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;

trait RequiresResourcePermission
{
    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::userCan(static::$viewPermission);
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::userCan(static::$createPermission);
    }

    public static function canEdit(Model $record): bool
    {
        return static::userCan(static::$updatePermission);
    }

    public static function canDelete(Model $record): bool
    {
        return static::userCan(static::$deletePermission);
    }

    public static function canDeleteAny(): bool
    {
        return static::userCan(static::$deletePermission);
    }

    protected static function userCan(?string $permission): bool
    {
        $user = auth()->user();

        return $permission !== null && $user !== null && $user->can($permission);
    }
}
