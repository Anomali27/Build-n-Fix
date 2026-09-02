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
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{id}', [App\Http\Controllers\CategoryController::class, 'show'])->name('categories.show');

// ── Admin ─────────────────────────────────────────────
Route::get('/admin/dashboard', function () {
    return '<h1>Admin Dashboard</h1><form method="POST" action="/logout">'.csrf_field().'<button>Logout</button></form>';
})->name('admin.dashboard');

// ── Owner ─────────────────────────────────────────────
Route::get('/owner/dashboard', function () {
    return '<h1>Owner Dashboard</h1><form method="POST" action="/logout">'.csrf_field().'<button>Logout</button></form>';
})->name('owner.dashboard');
