<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementAndDetailsTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $otherCustomer;
    private User $admin;
    private Branch $branch;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Serdam',
            'city' => 'Pontianak',
            'address' => 'Jl. Sungai Raya Dalam No. 88, Pontianak',
            'short_address' => 'Jl. Sungai Raya Dalam',
            'phone' => '0561-123456',
            'status' => 'open',
        ]);

        $this->product = Product::create([
            'sku' => 'SMN-001',
            'name' => 'Semen Portland 50 Kg Merah Putih',
            'brand' => 'Merah Putih',
            'unit' => 'Sak',
            'price' => 72000,
            'is_active' => true,
        ]);

        BranchStock::create([
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
            'price' => 72000,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'John Customer',
            'email' => 'customer@test.com',
            'role' => 'customer',
        ]);

        $this->otherCustomer = User::factory()->create([
            'name' => 'Jane Other',
            'email' => 'other@test.com',
            'role' => 'customer',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin Operasional',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'branch' => 'Serdam',
        ]);
    }

    public function test_customer_can_see_detail_pesanan_button_on_orders_index(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 'pickup', Order::STATUS_MENUNGGU_DIPROSES);

        $response = $this->actingAs($this->customer)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Detail Pesanan');
        $response->assertSee(route('orders.show', $order->order_number));
    }

    public function test_customer_can_view_own_order_detail(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 'pickup', Order::STATUS_MENUNGGU_DIPROSES);

        $response = $this->actingAs($this->customer)->get(route('orders.show', $order->order_number));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee('Semen Portland 50 Kg Merah Putih');
        $response->assertSee('Rp 72.000');
        $response->assertSee('Rp 144.000');
        $response->assertSee('Cabang Serdam');
        $response->assertSee('Pickup di Toko');
        $response->assertSee('Menunggu Diproses');
        $response->assertSee('Kembali ke Pesanan Saya');
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $order = $this->createOrderForCustomer($this->otherCustomer, 'pickup', Order::STATUS_MENUNGGU_DIPROSES);

        $response = $this->actingAs($this->customer)->get(route('orders.show', $order->order_number));

        $response->assertStatus(404);
    }

    public function test_customer_cannot_update_order_status(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 'pickup', Order::STATUS_MENUNGGU_DIPROSES);

        $response = $this->actingAs($this->customer)->put(route('orders.update', $order->order_number), [
            'order_status' => Order::STATUS_SEDANG_DISIAPKAN,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_delivery_order_status_and_it_records_history(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 'delivery', Order::STATUS_MENUNGGU_DIPROSES);

        $response = $this->actingAs($this->admin)->put(route('orders.update', $order->order_number), [
            'order_status' => Order::STATUS_SEDANG_DISIAPKAN,
            'notes' => 'Pesanan mulai disiapkan di gudang Serdam.',
        ]);

        $response->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals(Order::STATUS_SEDANG_DISIAPKAN, $order->order_status);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'user_id' => $this->admin->id,
            'status_from' => Order::STATUS_MENUNGGU_DIPROSES,
            'status_to' => Order::STATUS_SEDANG_DISIAPKAN,
            'notes' => 'Pesanan mulai disiapkan di gudang Serdam.',
        ]);
    }

    public function test_admin_cannot_set_pickup_status_on_delivery_order(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 'delivery', Order::STATUS_MENUNGGU_DIPROSES);

        $response = $this->actingAs($this->admin)->put(route('orders.update', $order->order_number), [
            'order_status' => Order::STATUS_SIAP_DIAMBIL,
        ]);

        $response->assertSessionHasErrors('order_status');
        $order->refresh();
        $this->assertEquals(Order::STATUS_MENUNGGU_DIPROSES, $order->order_status);
    }

    public function test_admin_cannot_set_delivery_status_on_pickup_order(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 'pickup', Order::STATUS_MENUNGGU_DIPROSES);

        $response = $this->actingAs($this->admin)->put(route('orders.update', $order->order_number), [
            'order_status' => Order::STATUS_SEDANG_DIANTAR,
        ]);

        $response->assertSessionHasErrors('order_status');
        $order->refresh();
        $this->assertEquals(Order::STATUS_MENUNGGU_DIPROSES, $order->order_status);
    }

    public function test_status_json_returns_realtime_data_for_polling(): void
    {
        $order = $this->createOrderForCustomer($this->customer, 'delivery', Order::STATUS_MENUNGGU_DIPROSES);

        $response = $this->actingAs($this->customer)->get(route('orders.status-json', $order->order_number));

        $response->assertStatus(200);
        $response->assertJson([
            'order_number' => $order->order_number,
            'order_status' => Order::STATUS_MENUNGGU_DIPROSES,
            'status_label' => 'Menunggu Diproses',
        ]);
    }

    private function createOrderForCustomer(User $customer, string $fulfillmentMethod, string $status): Order
    {
        $isDelivery = $fulfillmentMethod === 'delivery';
        $subtotal = 144000;
        $deliveryFee = $isDelivery ? 15000 : 0;
        $total = $subtotal + $deliveryFee;

        $order = Order::create([
            'order_number' => 'BNF-TEST-'.rand(100, 999),
            'user_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'fulfillment_method' => $fulfillmentMethod,
            'order_status' => $status,
            'payment_status' => 'paid',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '081234567890',
            'recipient_name' => $isDelivery ? $customer->name : null,
            'recipient_phone' => $isDelivery ? '081234567890' : null,
            'delivery_address' => $isDelivery ? 'Jl. Gajah Mada No. 45' : null,
            'delivery_city' => $isDelivery ? 'Pontianak' : null,
            'delivery_postal_code' => $isDelivery ? '78121' : null,
            'delivery_notes' => null,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount' => 0,
            'total' => $total,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'product_brand' => $this->product->brand,
            'product_unit' => $this->product->unit,
            'quantity' => 2,
            'unit_price' => 72000,
            'subtotal' => 144000,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'payment_code' => 'PAY-TEST-'.rand(100, 999),
            'payment_method' => 'Virtual Account Simulation',
            'amount' => $total,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'status_from' => null,
            'status_to' => $status,
            'notes' => 'Pesanan berhasil dibuat.',
        ]);

        return $order;
    }
}
