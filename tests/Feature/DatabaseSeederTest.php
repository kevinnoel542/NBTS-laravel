<?php

use App\Models\Article;
use App\Models\Badge;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\DonorProfile;
use App\Models\Reward;
use App\Models\User;
use App\PermissionName;
use App\RoleName;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('reference and demo seeders are idempotent and preserve existing credentials', function () {
    $this->seed(DatabaseSeeder::class);

    $staff = User::query()->where('email', 'staff@nbts.test')->sole();

    expect(Hash::check('Password123!', $staff->password))->toBeTrue();

    $staff->forceFill(['password' => Hash::make('Existing-cloned-password!')])->save();

    $this->seed(DatabaseSeeder::class);

    expect(Role::query()->count())->toBe(count(RoleName::cases()))
        ->and(Permission::query()->count())->toBe(count(PermissionName::cases()))
        ->and(BloodCenter::query()->whereIn('email', [
            'bloodbank@mnh.or.tz',
            'eastern@nbts.go.tz',
            'bloodbank@bmh.or.tz',
            'bloodbank@bugandomedicalcentre.go.tz',
        ])->count())->toBe(4)
        ->and(Badge::query()->count())->toBe(4)
        ->and(Reward::query()->count())->toBe(2)
        ->and(Article::query()->count())->toBe(3)
        ->and(User::query()->whereIn('email', [
            'admin@nbts.test',
            'manager@nbts.test',
            'staff@nbts.test',
            'donor@nbts.test',
        ])->count())->toBe(4)
        ->and(CenterStaff::query()->count())->toBe(2)
        ->and(DonorProfile::query()->count())->toBe(1)
        ->and(Hash::check('Existing-cloned-password!', $staff->fresh()->password))->toBeTrue();

    expect(User::query()->where('email', 'admin@nbts.test')->sole()->hasRole(RoleName::SuperAdmin->value))->toBeTrue()
        ->and(User::query()->where('email', 'manager@nbts.test')->sole()->hasRole(RoleName::CenterManager->value))->toBeTrue()
        ->and(User::query()->where('email', 'staff@nbts.test')->sole()->hasRole(RoleName::CenterStaff->value))->toBeTrue()
        ->and(User::query()->where('email', 'donor@nbts.test')->sole()->hasRole(RoleName::Donor->value))->toBeTrue();
});

test('demo data seeder is a no-op outside local and testing environments', function () {
    $originalEnvironment = app()->environment();
    app()->detectEnvironment(static fn (): string => 'production');

    try {
        app(DemoDataSeeder::class)->run();

        expect(User::query()->count())->toBe(0);
    } finally {
        app()->detectEnvironment(static fn (): string => $originalEnvironment);
    }
});
