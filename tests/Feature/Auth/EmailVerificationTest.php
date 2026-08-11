<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

test('email verification routes and feature are disabled during system construction', function () {
    expect(Features::enabled(Features::emailVerification()))->toBeFalse()
        ->and(Route::has('verification.notice'))->toBeFalse()
        ->and(Route::has('verification.verify'))->toBeFalse()
        ->and(Route::has('verification.send'))->toBeFalse();
});

test('staff with an unverified email can access the application', function () {
    $user = User::factory()->centerManager()->unverified()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});
