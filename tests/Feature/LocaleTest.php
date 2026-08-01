<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware('web')->get('/_test/current-locale', fn () => app()->getLocale());
});

test('a guest can switch between supported locales', function () {
    $this->from(route('home'))
        ->post(route('locale.update', ['locale' => 'sw']))
        ->assertRedirect(route('home'))
        ->assertSessionHas('locale', 'sw');

    $this->get('/_test/current-locale')
        ->assertOk()
        ->assertSeeText('sw');
});

test('an authenticated users locale is persisted', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)
        ->from(route('dashboard'))
        ->post(route('locale.update', ['locale' => 'sw']))
        ->assertRedirect(route('dashboard'));

    expect($user->refresh()->locale)->toBe('sw');

    $this->actingAs($user)
        ->get('/_test/current-locale')
        ->assertOk()
        ->assertSeeText('sw');
});

test('unsupported locales are rejected', function () {
    $this->post('/locale/fr')->assertNotFound();
});
