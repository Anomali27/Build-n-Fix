<?php

use App\Services\CartService;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    Session::forget('cart');
});

test('empty cart displays empty state on /cart', function () {
    $response = $this->get(route('cart.index'));

    $response->assertStatus(200);
    $response->assertSee('Your cart is empty');
    $response->assertSee('Start Shopping');
});

test('can add product to cart from valid branch', function () {
    $response = $this->post(route('cart.store'), [
        'product_id' => 1, // Semen Portland 40 Kg
        'quantity' => 2,
        'branch' => 1, // Serdam
    ]);

    $response->assertRedirect(route('cart.index'));
    $response->assertSessionHas('success');

    $cart = app(CartService::class)->getCart();
    expect($cart['item_count'])->toBe(1);
    expect($cart['total_quantity'])->toBe(2);
    expect($cart['branch_name'])->toBe('Serdam');
});

test('one branch per transaction rule prevents mixing products from different branches', function () {
    // Add product 1 from Branch 1 (Serdam)
    app(CartService::class)->addProduct(1, 1, 1);

    // Try adding product 2 from Branch 2 (Gajahmada)
    $response = $this->post(route('cart.store'), [
        'product_id' => 2,
        'quantity' => 1,
        'branch' => 2,
    ]);

    $response->assertSessionHas('branch_mismatch');
    $mismatch = session('branch_mismatch');
    expect($mismatch['existing_branch'])->toBe('Serdam');
    expect($mismatch['new_branch'])->toBe('Gajahmada');

    // Cart remains with Serdam product only
    $cart = app(CartService::class)->getCart();
    expect($cart['branch_name'])->toBe('Serdam');
    expect($cart['item_count'])->toBe(1);
});

test('can replace existing cart with new branch product using force parameter', function () {
    // Add product 1 from Branch 1 (Serdam)
    app(CartService::class)->addProduct(1, 1, 1);

    // Force add product 2 from Branch 2 (Gajahmada)
    $response = $this->post(route('cart.store'), [
        'product_id' => 2,
        'quantity' => 3,
        'branch' => 2,
        'force' => 1,
    ]);

    $response->assertRedirect(route('cart.index'));
    $cart = app(CartService::class)->getCart();
    expect($cart['branch_name'])->toBe('Gajahmada');
    expect($cart['item_count'])->toBe(1);
    expect($cart['total_quantity'])->toBe(3);
});

test('cannot add quantity exceeding branch stock', function () {
    // Product 1 stock at Branch 1 (Serdam) is 25
    $response = $this->post(route('cart.store'), [
        'product_id' => 1,
        'quantity' => 999,
        'branch' => 1,
    ]);

    $response->assertSessionHas('error', 'Quantity exceeds available stock.');
});

test('can update cart item quantity', function () {
    app(CartService::class)->addProduct(1, 2, 1);

    $response = $this->patch(route('cart.update', 1), [
        'quantity' => 5,
    ]);

    $response->assertRedirect(route('cart.index'));
    $cart = app(CartService::class)->getCart();
    expect($cart['total_quantity'])->toBe(5);
});

test('can remove product from cart', function () {
    app(CartService::class)->addProduct(1, 2, 1);

    $response = $this->delete(route('cart.destroy', 1));

    $response->assertRedirect(route('cart.index'));
    $cart = app(CartService::class)->getCart();
    expect($cart['item_count'])->toBe(0);
});

test('can clear cart completely', function () {
    app(CartService::class)->addProduct(1, 2, 1);

    $response = $this->delete(route('cart.clear'));

    $response->assertRedirect(route('cart.index'));
    $cart = app(CartService::class)->getCart();
    expect($cart['item_count'])->toBe(0);
    expect($cart['branch_id'])->toBeNull();
});
