<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Repositories\UserRepositories;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes — Build n Fix (Single Controller per Feature Multi-Role)
|--------------------------------------------------------------------------
*/

// ── Authentication ────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Quick Role Switcher (Convenient helper for testing multi-role workflows)
Route::get('/switch-role/{role}', function (string $role) {
    $roleMap = [
        'customer' => ['id' => 1, 'user_id' => 1, 'name' => 'Customer', 'email' => 'customer@buildnfix.test', 'role' => 'customer', 'branch' => null],
        'admin' => ['id' => 2, 'user_id' => 2, 'name' => 'Admin Serdam', 'email' => 'admin.serdam@buildnfix.test', 'role' => 'admin', 'branch' => 'Serdam'],
        'admin-gajahmada' => ['id' => 3, 'user_id' => 3, 'name' => 'Admin Gajahmada', 'email' => 'admin.gajahmada@buildnfix.test', 'role' => 'admin', 'branch' => 'Gajahmada'],
        'admin-kotabaru' => ['id' => 4, 'user_id' => 4, 'name' => 'Admin Kota Baru', 'email' => 'admin.kotabaru@buildnfix.test', 'role' => 'admin', 'branch' => 'Kota Baru'],
        'owner' => ['id' => 5, 'user_id' => 5, 'name' => 'Owner', 'email' => 'owner@buildnfix.test', 'role' => 'owner', 'branch' => 'all'],
    ];

    $target = $roleMap[$role] ?? $roleMap['customer'];
    app(\App\Services\AuthService::class)->createSession($target);

    return redirect()->back()->with('success', 'Beralih peran sebagai: ' . $target['name'] . ' (' . ucfirst($target['role']) . ')');
})->name('switch-role');

// ── Dashboard ─────────────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/admin/dashboard', fn () => redirect()->route('dashboard'));
Route::get('/owner/dashboard', fn () => redirect()->route('dashboard'));

// ── Customer Home & Locations ─────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');

// ── Products (Single Controller) ──────────────────────
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

// ── Categories (Single Controller) ────────────────────
Route::resource('categories', CategoryController::class);
Route::get('/categories/{category}/{product}', [ProductController::class, 'show'])->name('categories.products.show');

// ── Stock (Single Controller: 1 Card = 1 Produk) ──────
Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');

// ── Stock Movements (Single Controller) ───────────────
Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
Route::get('/stock-movement', fn () => redirect()->route('stock-movements.index'));

// ── Suppliers (Single Controller) ─────────────────────
Route::resource('suppliers', SupplierController::class);
Route::post('/suppliers/{supplier}/assign-product', [SupplierController::class, 'assignProduct'])->name('suppliers.assign-product');

// ── Orders (Single Controller) ────────────────────────
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');

// ── Payments (Single Controller) ──────────────────────
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');

// ── Reports (Single Controller: Laporan Penjualan) ────
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

// ── Cart & Checkout ───────────────────────────────────
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/', [CartController::class, 'store'])->name('store');
    Route::patch('/{product}', [CartController::class, 'update'])->name('update');
    Route::delete('/{product}', [CartController::class, 'destroy'])->name('destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
    Route::get('/payment', [CheckoutController::class, 'payment'])->name('payment');
    Route::post('/payment', [CheckoutController::class, 'processPayment'])->name('process-payment');
});

// ── Profile ───────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
});
