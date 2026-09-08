<?php

use App\Repositories\UserRepositories;
use Illuminate\Support\Facades\Session;

test('guest cannot access profile page and is redirected to login', function () {
    $response = $this->get(route('profile.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can view profile page with account details', function () {
    $this->post(route('login.store'), [
        'email' => 'customer@buildnfix.test',
        'password' => 'password',
    ]);

    $response = $this->get(route('profile.index'));

    $response->assertStatus(200);
    $response->assertSee('Profile Settings');
    $response->assertSee('Customer');
    $response->assertSee('customer@buildnfix.test');
    $response->assertSee('#USR-001');
});

test('user can update full name and session is updated', function () {
    $this->post(route('login.store'), [
        'email' => 'customer@buildnfix.test',
        'password' => 'password',
    ]);

    $response = $this->put(route('profile.update'), [
        'name' => 'Edward Cornelius',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Profile updated successfully.');

    $user = Session::get('user');
    expect($user['name'])->toBe('Edward Cornelius');

    $userData = UserRepositories::find(1);
    expect($userData['name'])->toBe('Edward Cornelius');
});

test('updating profile name requires non-empty name', function () {
    $this->post(route('login.store'), [
        'email' => 'customer@buildnfix.test',
        'password' => 'password',
    ]);

    $response = $this->put(route('profile.update'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
});

test('user can update account password with correct current password', function () {
    $this->post(route('login.store'), [
        'email' => 'customer@buildnfix.test',
        'password' => 'password',
    ]);

    $response = $this->put(route('profile.password'), [
        'current_password' => 'password',
        'new_password' => 'newsecret123',
        'confirm_password' => 'newsecret123',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Password updated successfully.');

    expect(UserRepositories::verifyPassword(1, 'newsecret123'))->toBeTrue();
});

test('password update fails with wrong current password', function () {
    $this->post(route('login.store'), [
        'email' => 'customer@buildnfix.test',
        'password' => 'password',
    ]);

    $response = $this->put(route('profile.password'), [
        'current_password' => 'wrongpass',
        'new_password' => 'newsecret123',
        'confirm_password' => 'newsecret123',
    ]);

    $response->assertSessionHas('error');
});

test('logout invalidates session and redirects to login', function () {
    $this->post(route('login.store'), [
        'email' => 'customer@buildnfix.test',
        'password' => 'password',
    ]);

    $response = $this->post(route('logout'));

    $response->assertRedirect(route('login'));
    $this->assertFalse(Session::has('user'));
});
