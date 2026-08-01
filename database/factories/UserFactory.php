<?php

namespace Database\Factories;

use App\Models\User;
use App\RoleName;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if ($user->roles()->exists()) {
                return;
            }

            $roleName = match ($user->role) {
                'admin' => RoleName::SuperAdmin->value,
                'staff' => RoleName::CenterStaff->value,
                default => RoleName::Donor->value,
            };

            $user->assignRole(Role::findOrCreate($roleName, 'web'));
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
            'locale' => 'en',
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function donor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'donor',
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'staff',
        ]);
    }

    public function centerManager(): static
    {
        return $this->staff()->afterCreating(function (User $user): void {
            $user->syncRoles([Role::findOrCreate(RoleName::CenterManager->value, 'web')]);
        });
    }

    public function nbtsAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ])->afterCreating(function (User $user): void {
            $user->syncRoles([Role::findOrCreate(RoleName::NbtsAdmin->value, 'web')]);
        });
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
