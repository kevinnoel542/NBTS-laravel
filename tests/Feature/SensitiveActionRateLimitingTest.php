<?php

use App\Http\Middleware\ThrottleSensitiveAuthenticationActions;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Security;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

test('authentication and sensitive account routes carry their required limiters', function () {
    $namedLimiters = [
        'login.store' => 'throttle:login',
        'two-factor.login.store' => 'throttle:two-factor',
        'passkey.login' => 'throttle:passkeys',
        'passkey.confirm' => 'throttle:passkeys',
        'passkey.store' => 'throttle:passkeys',
    ];

    foreach ($namedLimiters as $routeName => $middleware) {
        expect(Route::getRoutes()->getByName($routeName)?->gatherMiddleware())
            ->toContain($middleware);
    }

    foreach ([
        'password.email',
        'password.update',
        'password.confirm.store',
        'two-factor.enable',
        'two-factor.confirm',
        'two-factor.disable',
        'two-factor.regenerate-recovery-codes',
        'passkey.destroy',
    ] as $routeName) {
        $route = Route::getRoutes()->getByName($routeName);

        expect($route)->not->toBeNull()
            ->and(app('router')->gatherRouteMiddleware($route))->toContain(ThrottleSensitiveAuthenticationActions::class);
    }
});

test('password reset link requests are rate limited', function () {
    Notification::fake();

    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect();
    }

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertStatus(429);
});

test('livewire password updates are rate limited independently of route throttles', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(Security::class);

    foreach (range(1, 5) as $attempt) {
        $component
            ->set('current_password', 'incorrect-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasErrors('current_password');
    }

    $component
        ->set('current_password', 'incorrect-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasErrors('rateLimit');
});

test('livewire email verification resends are rate limited', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $component = Livewire::test(Profile::class);

    foreach (range(1, 5) as $attempt) {
        $component
            ->call('resendVerificationNotification')
            ->assertHasNoErrors();
    }

    $component
        ->call('resendVerificationNotification')
        ->assertHasErrors('rateLimit');
});
