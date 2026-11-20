<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Seed initial realistic orders, line items, and payments.
     */
    public function run(): void
    {
        $customer = User::query()
            ->where('email', 'customer@gmail.com')
            ->first()
            ?? User::query()->where('role', 'customer')->first();

        $admin = User::query()->where('role', 'admin')->first();

        $branchesByName = Branch::query()->get()->keyBy('name');
        $productsBySku = Product::query()->get()->keyBy('sku');
        $fallbackBranch = Branch::query()->orderBy('id')->first();
        $fallbackProduct = Product::query()->orderBy('id')->first();

        if (! $fallbackBranch || ! $fallbackProduct) {
            return;
        }

        $entries = [
            [
                'order_number' => 'BNF-20260902-001',
                'branch_name' => 'Serdam',
                'fulfillment_method' => 'pickup',
                'order_status' => Order::STATUS_SIAP_DIAMBIL,
                'payment_status' => 'paid',
                'payment_method' => 'Virtual Account (BCA / Mandiri / BRI Simulation)',
                'delivery_fee' => 0,
                'created_at' => '2026-09-02 10:30:00',
                'histories' => [
                    ['status_from' => null, 'status_to' => Order::STATUS_MENUNGGU_DIPROSES, 'offset_minutes' => 0, 'notes' => 'Pesanan berhasil dibuat dan pembayaran dikonfirmasi.'],
                    ['status_from' => Order::STATUS_MENUNGGU_DIPROSES, 'status_to' => Order::STATUS_SEDANG_DISIAPKAN, 'offset_minutes' => 15, 'notes' => 'Pesanan mulai disiapkan oleh petugas gudang cabang Serdam.'],
                    ['status_from' => Order::STATUS_SEDANG_DISIAPKAN, 'status_to' => Order::STATUS_SEDANG_DIKEMAS, 'offset_minutes' => 45, 'notes' => 'Barang pesanan telah dikemas rapi dan aman.'],
                    ['status_from' => Order::STATUS_SEDANG_DIKEMAS, 'status_to' => Order::STATUS_SIAP_DIAMBIL, 'offset_minutes' => 60, 'notes' => 'Barang sudah siap diambil di kasir cabang Serdam.'],
                ],
                'items' => [
                    ['sku' => 'SMN-001', 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'BNF-20260902-002',
                'branch_name' => 'Gajahmada',
                'fulfillment_method' => 'delivery',
                'order_status' => Order::STATUS_SEDANG_DIANTAR,
                'payment_status' => 'paid',
                'payment_method' => 'E-Wallet (GoPay / OVO / Dana Simulation)',
                'delivery_address' => 'Jl. Gajah Mada No. 45',
                'delivery_city' => 'Pontianak',
                'delivery_postal_code' => '78121',
                'delivery_fee' => 15000,
                'created_at' => '2026-09-02 14:15:00',
                'histories' => [
                    ['status_from' => null, 'status_to' => Order::STATUS_MENUNGGU_DIPROSES, 'offset_minutes' => 0, 'notes' => 'Pesanan berhasil dibuat dan pembayaran dikonfirmasi.'],
                    ['status_from' => Order::STATUS_MENUNGGU_DIPROSES, 'status_to' => Order::STATUS_SEDANG_DISIAPKAN, 'offset_minutes' => 15, 'notes' => 'Toko cabang Gajahmada mulai menyiapkan material.'],
                    ['status_from' => Order::STATUS_SEDANG_DISIAPKAN, 'status_to' => Order::STATUS_SEDANG_DIKEMAS, 'offset_minutes' => 40, 'notes' => 'Barang pesanan sedang dikemas aman untuk pengiriman.'],
                    ['status_from' => Order::STATUS_SEDANG_DIKEMAS, 'status_to' => Order::STATUS_SIAP_DIKIRIM, 'offset_minutes' => 65, 'notes' => 'Barang pesanan telah siap dijemput oleh armada pengantaran.'],
                    ['status_from' => Order::STATUS_SIAP_DIKIRIM, 'status_to' => Order::STATUS_SEDANG_DIANTAR, 'offset_minutes' => 90, 'notes' => 'Armada kurir sedang dalam perjalanan menuju alamat pengiriman.'],
                ],
                'items' => [
                    ['sku' => 'SMN-007', 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'BNF-20260828-003',
                'branch_name' => 'Kota Baru',
                'fulfillment_method' => 'delivery',
                'order_status' => Order::STATUS_PESANAN_SELESAI,
                'payment_status' => 'paid',
                'payment_method' => 'Virtual Account (BCA / Mandiri / BRI Simulation)',
                'delivery_address' => 'Jl. Perdana No. 88',
                'delivery_city' => 'Pontianak',
                'delivery_postal_code' => '78124',
                'delivery_fee' => 15000,
                'created_at' => '2026-08-28 09:00:00',
                'histories' => [
                    ['status_from' => null, 'status_to' => Order::STATUS_MENUNGGU_DIPROSES, 'offset_minutes' => 0, 'notes' => 'Pesanan berhasil dibuat dan pembayaran dikonfirmasi.'],
                    ['status_from' => Order::STATUS_MENUNGGU_DIPROSES, 'status_to' => Order::STATUS_SEDANG_DISIAPKAN, 'offset_minutes' => 20, 'notes' => 'Pesanan mulai disiapkan di gudang Kota Baru.'],
                    ['status_from' => Order::STATUS_SEDANG_DISIAPKAN, 'status_to' => Order::STATUS_SEDANG_DIKEMAS, 'offset_minutes' => 50, 'notes' => 'Barang telah selesai dikemas.'],
                    ['status_from' => Order::STATUS_SEDANG_DIKEMAS, 'status_to' => Order::STATUS_SIAP_DIKIRIM, 'offset_minutes' => 70, 'notes' => 'Barang siap dikirim ke alamat tujuan.'],
                    ['status_from' => Order::STATUS_SIAP_DIKIRIM, 'status_to' => Order::STATUS_SEDANG_DIANTAR, 'offset_minutes' => 95, 'notes' => 'Pesanan sedang diantar oleh kurir toko.'],
                    ['status_from' => Order::STATUS_SEDANG_DIANTAR, 'status_to' => Order::STATUS_PESANAN_SELESAI, 'offset_minutes' => 140, 'notes' => 'Pesanan telah diterima customer dengan baik. Transaksi selesai.'],
                ],
                'items' => [
                    ['sku' => 'SMN-001', 'quantity' => 5],
                    ['sku' => 'CAT-001', 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'BNF-20260902-004',
                'branch_name' => 'Serdam',
                'fulfillment_method' => 'pickup',
                'order_status' => Order::STATUS_MENUNGGU_DIPROSES,
                'payment_status' => 'paid',
                'payment_method' => 'Credit / Debit Simulation',
                'delivery_fee' => 0,
                'created_at' => '2026-09-02 18:45:00',
                'histories' => [
                    ['status_from' => null, 'status_to' => Order::STATUS_MENUNGGU_DIPROSES, 'offset_minutes' => 0, 'notes' => 'Pesanan berhasil dibuat dan pembayaran dikonfirmasi.'],
                ],
                'items' => [
                    ['sku' => 'SMN-004', 'quantity' => 1],
                ],
            ],
        ];

        foreach ($entries as $entry) {
            /** @var Branch $branch */
            $branch = $branchesByName[$entry['branch_name']] ?? $fallbackBranch;

            $subtotal = 0;
            $preparedItems = [];

            foreach ($entry['items'] as $itemSpec) {
                /** @var Product $product */
                $product = $productsBySku[$itemSpec['sku']] ?? $fallbackProduct;
                $branchPrice = BranchStock::query()
                    ->where('product_id', $product->id)
                    ->where('branch_id', $branch->id)
                    ->value('price');

                $unitPrice = (int) ($branchPrice && $branchPrice > 0 ? $branchPrice : $product->price);
                $qty = (int) $itemSpec['quantity'];
                $lineSubtotal = $unitPrice * $qty;
                $subtotal += $lineSubtotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'product_brand' => $product->brand ?? 'Build n Fix',
                    'product_unit' => $product->unit ?? $product->size ?? 'Unit',
                    'product_image' => $product->image,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $deliveryFee = (int) $entry['delivery_fee'];
            $total = $subtotal + $deliveryFee;
            $customerName = $customer?->name ?? 'Customer';
            $customerEmail = $customer?->email ?? 'customer@gmail.com';
            $customerPhone = $customer?->phone ?? '081234567890';

            $order = Order::updateOrCreate(
                ['order_number' => $entry['order_number']],
                [
                    'user_id' => $customer?->id,
                    'branch_id' => $branch->id,
                    'fulfillment_method' => $entry['fulfillment_method'],
                    'order_status' => $entry['order_status'],
                    'payment_status' => $entry['payment_status'],
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'customer_phone' => $customerPhone,
                    'recipient_name' => $entry['fulfillment_method'] === 'delivery' ? $customerName : null,
                    'recipient_phone' => $entry['fulfillment_method'] === 'delivery' ? $customerPhone : null,
                    'delivery_address' => $entry['delivery_address'] ?? null,
                    'delivery_city' => $entry['delivery_city'] ?? null,
                    'delivery_postal_code' => $entry['delivery_postal_code'] ?? null,
                    'delivery_notes' => null,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'discount' => 0,
                    'total' => $total,
                ]
            );

            $order->forceFill([
                'created_at' => $entry['created_at'],
                'updated_at' => $entry['created_at'],
            ])->saveQuietly();

            $order->items()->delete();
            foreach ($preparedItems as $itemData) {
                OrderItem::create(array_merge(['order_id' => $order->id], $itemData));
            }

            // Seed status histories
            $order->statusHistories()->delete();
            $baseTime = Carbon::parse($entry['created_at']);
            foreach ($entry['histories'] as $h) {
                $hTime = $baseTime->copy()->addMinutes($h['offset_minutes']);
                $order->statusHistories()->create([
                    'user_id' => $admin?->id ?? $customer?->id,
                    'status_from' => $h['status_from'],
                    'status_to' => $h['status_to'],
                    'notes' => $h['notes'],
                    'created_at' => $hTime,
                    'updated_at' => $hTime,
                ]);
            }

            $paymentCode = 'PAY-'.str_replace('BNF-', '', $entry['order_number']);
            $payment = Payment::updateOrCreate(
                ['payment_code' => $paymentCode],
                [
                    'order_id' => $order->id,
                    'user_id' => $customer?->id,
                    'branch_id' => $branch->id,
                    'payment_method' => $entry['payment_method'],
                    'amount' => $total,
                    'status' => $entry['payment_status'],
                    'paid_at' => $entry['payment_status'] === 'paid' ? $entry['created_at'] : null,
                    'proof_url' => null,
                ]
            );

            $payment->forceFill([
                'created_at' => $entry['created_at'],
                'updated_at' => $entry['created_at'],
            ])->saveQuietly();
        }
    }
}
