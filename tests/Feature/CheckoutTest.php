<?php

use App\Data\BranchStockData;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    Session::forget('cart');
    Session::forget('checkout');
    Session::forget('branch_stock_overrides');
    Session::put('user', [
        'user_id' => 1,
        'name' => 'Customer Test',
        'email' => 'customer@buildnfix.test',
        'phone' => '081234567890',
    ]);
});

test('unauthenticated user is redirected to login', function () {
    Session::forget('user');

    $response = $this->get(route('checkout.index'));

    $response->assertRedirect(route('login'));
});

test('checkout index renders correctly with cart items', function () {
    app(CartService::class)->addProduct(1, 2, 1); // Semen Portland at Serdam

    $response = $this->get(route('checkout.index'));

    $response->assertStatus(200);
    $response->assertSee('Build n Fix Checkout');
    $response->assertSee('Order Branch: Cabang Serdam');
    $response->assertSee('Pickup di Toko');
    $response->assertSee('Delivery');
    $response->assertSee('Semen Portland 40 Kg');
});

test('store pickup fulfillment saves checkout data with zero delivery fee', function () {
    app(CartService::class)->addProduct(1, 2, 1);

    $response = $this->post(route('checkout.store'), [
        'customer_name' => 'John Construction',
        'customer_email' => 'john@test.com',
        'customer_phone' => '081234567890',
        'fulfillment_method' => 'pickup',
    ]);

    $response->assertRedirect(route('checkout.payment'));

    $checkoutData = app(CheckoutService::class)->getCheckoutData();
    expect($checkoutData['fulfillment_method'])->toBe('pickup');
    expect($checkoutData['delivery_fee'])->toBe(0);
    expect($checkoutData['total'])->toBe(130000);
});

test('store delivery fulfillment validates address and calculates delivery fee', function () {
    app(CartService::class)->addProduct(1, 2, 1);

    // Missing street address
    $invalidResponse = $this->post(route('checkout.store'), [
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@test.com',
        'customer_phone' => '081234567890',
        'fulfillment_method' => 'delivery',
        'recipient_name' => 'Jane Doe',
        'phone' => '081234567890',
        'address' => '',
        'city' => 'Pontianak',
    ]);

    $invalidResponse->assertSessionHasErrors(['address']);

    // Valid delivery address
    $validResponse = $this->post(route('checkout.store'), [
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@test.com',
        'customer_phone' => '081234567890',
        'fulfillment_method' => 'delivery',
        'recipient_name' => 'Jane Doe',
        'phone' => '081234567890',
        'address' => 'Jl. Ahmad Yani No. 50',
        'city' => 'Pontianak',
        'postal_code' => '78122',
    ]);

    $validResponse->assertRedirect(route('checkout.payment'));

    $checkoutData = app(CheckoutService::class)->getCheckoutData();
    expect($checkoutData['fulfillment_method'])->toBe('delivery');
    expect($checkoutData['delivery_fee'])->toBe(15000);
    expect($checkoutData['total'])->toBe(145000); // 130000 + 15000
});

test('payment simulation failure keeps cart intact and does not decrease stock', function () {
    app(CartService::class)->addProduct(1, 2, 1); // 2 Qty of product 1 at branch 1 (stock initial 25)
    app(CheckoutService::class)->preparePayment([
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@customer.com',
        'customer_phone' => '081234567890',
        'fulfillment_method' => 'pickup',
    ]);

    $initialStock = BranchStockData::getStock(1, 1);

    $response = $this->post(route('checkout.process-payment'), [
        'payment_method' => 'Virtual Account',
        'simulation_outcome' => 'failed',
    ]);

    $response->assertRedirect(route('checkout.payment'));
    $response->assertSessionHas('payment_failed');

    $cart = app(CartService::class)->getCart();
    expect($cart['item_count'])->toBe(1);

    $stockAfterFailure = BranchStockData::getStock(1, 1);
    expect($stockAfterFailure)->toBe($initialStock);
});

test('payment simulation success creates order, decrements stock for selected branch, and redirects to orders center', function () {
    app(CartService::class)->addProduct(1, 2, 1); // 2 Qty of product 1 at Serdam (branch 1)
    app(CheckoutService::class)->preparePayment([
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@customer.com',
        'customer_phone' => '081234567890',
        'fulfillment_method' => 'pickup',
    ]);

    $stockSerdamBefore = BranchStockData::getStock(1, 1);
    $stockGajahmadaBefore = BranchStockData::getStock(1, 2);

    $response = $this->post(route('checkout.process-payment'), [
        'payment_method' => 'Virtual Account (BCA)',
        'simulation_outcome' => 'success',
    ]);

    $response->assertRedirect(route('orders.index', ['tab' => 'success']));
    $response->assertSessionHas('payment_success');

    // Cart and checkout session must be cleared
    $cart = app(CartService::class)->getCart();
    expect($cart['item_count'])->toBe(0);

    // Stock for Serdam (branch 1) decreased by 2, Gajahmada (branch 2) unchanged
    $stockSerdamAfter = BranchStockData::getStock(1, 1);
    $stockGajahmadaAfter = BranchStockData::getStock(1, 2);

    expect($stockSerdamAfter)->toBe($stockSerdamBefore - 2);
    expect($stockGajahmadaAfter)->toBe($stockGajahmadaBefore);
});
