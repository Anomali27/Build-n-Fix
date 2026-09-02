<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Build n Fix
|--------------------------------------------------------------------------
*/

// ── Authentication ────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Customer ──────────────────────────────────────────
Route::get('/', function () {
    $user = session('user');
    $name = $user['name'] ?? 'Customer';
    $role = $user['role'] ?? 'guest';

    return '<html><body style="font-family:sans-serif;padding:2rem;">
        <h2>Welcome, '.e($name).'!</h2>
        <p>Role: '.e($role).'</p>
        <form method="POST" action="/logout">'.csrf_field().'
            <button type="submit">Logout</button>
        </form>
        <p style="color:#888;font-size:0.85rem;">(Home page placeholder — will be built in a future prompt)</p>
    </body></html>';
})->name('home');

// ── Admin ─────────────────────────────────────────────
Route::get('/admin/dashboard', function () {
    return '<h1>Admin Dashboard</h1><form method="POST" action="/logout">'.csrf_field().'<button>Logout</button></form>';
})->name('admin.dashboard');

// ── Owner ─────────────────────────────────────────────
Route::get('/owner/dashboard', function () {
    return '<h1>Owner Dashboard</h1><form method="POST" action="/logout">'.csrf_field().'<button>Logout</button></form>';
})->name('owner.dashboard');
