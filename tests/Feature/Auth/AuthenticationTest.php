<?php

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Passkeys;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response
        ->assertOk()
        ->assertDontSee('Sign up');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('inactive staff accounts can not authenticate on the web', function () {
    $user = User::factory()->inactive()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('donor accounts can not authenticate on the staff web account', function () {
    $user = User::factory()->donor()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('passkey authorization rejects inactive and donor accounts', function () {
    $request = Request::create('/passkeys/login', 'POST');

    foreach ([User::factory()->inactive()->create(), User::factory()->donor()->create()] as $user) {
        $passkey = new Passkey;
        $passkey->setRelation('user', $user);

        expect(Passkeys::allowsLogin($request, $passkey))->toBeFalse();
    }

    $staffUser = User::factory()->create();
    $staffPasskey = new Passkey;
    $staffPasskey->setRelation('user', $staffUser);

    expect(Passkeys::allowsLogin($request, $staffPasskey))->toBeTrue();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
