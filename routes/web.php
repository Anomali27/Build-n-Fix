<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
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
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])
        ->name('index');

    Route::get('/create', [CategoryController::class, 'create'])
        ->middleware('role:admin,owner')
        ->name('create');

    Route::post('/', [CategoryController::class, 'store'])
        ->middleware('role:admin,owner')
        ->name('store');

    Route::get('/{category}', [CategoryController::class, 'show'])
        ->name('show');

    Route::get('/{category}/edit', [CategoryController::class, 'edit'])
        ->middleware('role:admin,owner')
        ->name('edit');

    Route::put('/{category}', [CategoryController::class, 'update'])
        ->middleware('role:admin,owner')
        ->name('update');

    Route::delete('/{category}', [CategoryController::class, 'destroy'])
        ->middleware('role:admin,owner')
        ->name('destroy');

    // Product Detail under category: /categories/{category}/{product}
    Route::get('/{category}/{product}', [ProductController::class, 'show'])
        ->name('products.show');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// ── Admin ─────────────────────────────────────────────
Route::get('/admin/dashboard', function () {
    return '<h1>Admin Dashboard</h1><form method="POST" action="/logout">'.csrf_field().'<button>Logout</button></form>';
})->name('admin.dashboard');

// ── Owner ─────────────────────────────────────────────
Route::get('/owner/dashboard', function () {
    return '<h1>Owner Dashboard</h1><form method="POST" action="/logout">'.csrf_field().'<button>Logout</button></form>';
})->name('owner.dashboard');
