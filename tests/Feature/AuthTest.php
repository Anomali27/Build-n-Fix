<?php

use App\Data\UserData;
use Illuminate\Support\Facades\Session;

// ── Login Page ──────────────────────────────────────────────────────────────

test('login page renders for guest', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('Welcome Back');
});

test('login validates required fields', function () {
    $response = $this->post(route('login.store'), []);

    $response->assertSessionHasErrors(['email', 'password']);
});

test('login rejects invalid credentials without creating session', function () {
    $response = $this->post(route('login.store'), [
        'email' => 'customer@buildnfix.test',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHas('auth_error');
    $this->assertFalse(Session::has('user'));
});

test('customer login succeeds and redirects to home, password not in session', function () {
    $response = $this->post(route('login.store'), [
        'email' => 'customer@buildnfix.test',
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertTrue(Session::has('user'));

    $user = Session::get('user');
    expect($user['role'])->toBe('customer');
    expect($user['email'])->toBe('customer@buildnfix.test');
    expect($user)->not->toHaveKey('password');
});

test('admin serdam login redirects to admin dashboard', function () {
    $response = $this->post(route('login.store'), [
        'email' => 'admin.serdam@buildnfix.test',
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin/dashboard');

    $user = Session::get('user');
    expect($user['role'])->toBe('admin');
    expect($user['branch'])->toBe('Serdam');
    expect($user)->not->toHaveKey('password');
});

test('owner login redirects to owner dashboard', function () {
    $response = $this->post(route('login.store'), [
        'email' => 'owner@buildnfix.test',
        'password' => 'password',
    ]);

    $response->assertRedirect('/owner/dashboard');

    $user = Session::get('user');
    expect($user['role'])->toBe('owner');
    expect($user['branch'])->toBe('all');
});

test('authenticated user accessing login is redirected', function () {
    Session::put('user', [
        'user_id' => 1,
        'name' => 'Customer',
        'email' => 'customer@buildnfix.test',
        'role' => 'customer',
        'branch' => null,
    ]);

    $this->get(route('login'))->assertRedirect('/');
});

// ── Register Page ───────────────────────────────────────────────────────────

test('register page renders for guest', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
    $response->assertSee('Create Your Account');
});

test('register validates required fields', function () {
    $response = $this->post(route('register.store'), [
        'name' => '',
        'email' => 'not-an-email',
        'phone' => '',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'phone', 'password', 'terms']);
});

test('register rejects duplicate email', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'customer@buildnfix.test',
        'phone' => '081234567890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'terms' => '1',
    ]);

    $response->assertSessionHas('auth_error');
});

test('register creates customer account with forced customer role and logs them in', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'phone' => '081234567890',
        'password' => 'securepassword',
        'password_confirmation' => 'securepassword',
        'terms' => '1',
    ]);

    $response->assertRedirect('/');

    $user = Session::get('user');
    expect($user['name'])->toBe('Budi Santoso');
    expect($user['email'])->toBe('budi@example.com');
    expect($user['role'])->toBe('customer');
    expect($user['branch'])->toBeNull();
    expect($user)->not->toHaveKey('password');

    // Verify stored in UserData
    $found = UserData::findByEmail('budi@example.com');
    expect($found)->not->toBeNull();
    expect($found['role'])->toBe('customer');
});

test('newly registered user can log in after registration', function () {
    // Register first
    $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '081234567891',
        'password' => 'securepassword',
        'password_confirmation' => 'securepassword',
        'terms' => '1',
    ]);

    // Log out
    Session::forget('user');

    // Log back in
    $response = $this->post(route('login.store'), [
        'email' => 'jane@example.com',
        'password' => 'securepassword',
    ]);

    $response->assertRedirect('/');
    expect(Session::get('user.email'))->toBe('jane@example.com');
});
