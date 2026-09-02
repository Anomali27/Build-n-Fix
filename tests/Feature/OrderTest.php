<?php

use App\Services\OrderService;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    Session::put('user', [
        'user_id' => 1,
        'name' => 'Customer',
        'email' => 'customer@buildnfix.test',
        'role' => 'customer',
        'branch' => null,
    ]);
});

test('guest accessing orders page is redirected to login', function () {
    Session::forget('user');

    $response = $this->get(route('orders.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated customer can access order center index page', function () {
    $response = $this->get(route('orders.index'));

    $response->assertStatus(200);
    $response->assertSee('My Orders');
    $response->assertSee('Semua Pesanan');
    $response->assertSee('Berhasil');
    $response->assertSee('Tracking');
    $response->assertSee('Riwayat');
});

test('order tabs render correctly with query parameters', function () {
    $this->get(route('orders.index', ['tab' => 'all']))->assertStatus(200);
    $this->get(route('orders.index', ['tab' => 'success']))->assertStatus(200);
    $this->get(route('orders.index', ['tab' => 'tracking']))->assertStatus(200);
    $this->get(route('orders.index', ['tab' => 'history']))->assertStatus(200);
});

test('sub-filtering by fulfillment method works', function () {
    $response = $this->get(route('orders.index', ['tab' => 'all', 'filter' => 'pickup']));
    $response->assertStatus(200);

    $responseDelivery = $this->get(route('orders.index', ['tab' => 'all', 'filter' => 'delivery']));
    $responseDelivery->assertStatus(200);
});

test('order detail page /orders/{order} renders selected order detail', function () {
    $response = $this->get(route('orders.show', 'BNF-20260902-001'));

    $response->assertStatus(200);
    $response->assertSee('BNF-20260902-001');
    $response->assertSee('Serdam');
});

test('order service generates correct timeline for pickup vs delivery', function () {
    $service = new OrderService;

    $pickupOrder = [
        'fulfillment_method' => 'pickup',
        'order_status' => 'ready_to_pick_up',
        'branch_name' => 'Serdam',
    ];
    $pickupTimeline = $service->getTrackingTimeline($pickupOrder);

    expect(count($pickupTimeline))->toBe(3);
    expect($pickupTimeline[0]['key'])->toBe('paid');
    expect($pickupTimeline[1]['key'])->toBe('ready_to_pick_up');
    expect($pickupTimeline[1]['current'])->toBeTrue();

    $deliveryOrder = [
        'fulfillment_method' => 'delivery',
        'order_status' => 'on_delivery',
        'branch_name' => 'Gajahmada',
    ];
    $deliveryTimeline = $service->getTrackingTimeline($deliveryOrder);

    expect(count($deliveryTimeline))->toBe(4);
    expect($deliveryTimeline[0]['key'])->toBe('paid');
    expect($deliveryTimeline[1]['key'])->toBe('proses');
    expect($deliveryTimeline[2]['key'])->toBe('on_delivery');
    expect($deliveryTimeline[2]['current'])->toBeTrue();
});
