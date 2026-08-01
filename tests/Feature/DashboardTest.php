<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('donors can not visit the staff dashboard', function () {
    $user = User::factory()->donor()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('inactive users are logged out of the staff dashboard', function () {
    $user = User::factory()->inactive()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
