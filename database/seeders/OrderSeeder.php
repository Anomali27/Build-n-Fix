<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Seed initial realistic orders, line items, and payments.
     */
    public function run(): void
    {
        $customer = User::query()
            ->where('email', 'customer@buildnfix.test')
            ->first()
            ?? User::query()->where('role', 'customer')->first();

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
                'order_status' => 'ready_to_pick_up',
                'payment_status' => 'paid',
                'payment_method' => 'Virtual Account (BCA / Mandiri / BRI Simulation)',
                'delivery_fee' => 0,
                'created_at' => '2026-09-02 10:30:00',
                'items' => [
                    ['sku' => 'SMN-001', 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'BNF-20260902-002',
                'branch_name' => 'Gajahmada',
                'fulfillment_method' => 'delivery',
                'order_status' => 'on_delivery',
                'payment_status' => 'paid',
                'payment_method' => 'E-Wallet (GoPay / OVO / Dana Simulation)',
                'delivery_address' => 'Jl. Gajah Mada No. 45',
                'delivery_city' => 'Pontianak',
                'delivery_postal_code' => '78121',
                'delivery_fee' => 15000,
                'created_at' => '2026-09-02 14:15:00',
                'items' => [
                    ['sku' => 'SMN-007', 'quantity' => 2],
                ],
            ],
            [
                'order_number' => 'BNF-20260828-003',
                'branch_name' => 'Kota Baru',
                'fulfillment_method' => 'delivery',
                'order_status' => 'order_completed',
                'payment_status' => 'paid',
                'payment_method' => 'Virtual Account (BCA / Mandiri / BRI Simulation)',
                'delivery_address' => 'Jl. Perdana No. 88',
                'delivery_city' => 'Pontianak',
                'delivery_postal_code' => '78124',
                'delivery_fee' => 15000,
                'created_at' => '2026-08-28 09:00:00',
                'items' => [
                    ['sku' => 'SMN-001', 'quantity' => 5],
                    ['sku' => 'CAT-001', 'quantity' => 1],
                ],
            ],
            [
                'order_number' => 'BNF-20260902-004',
                'branch_name' => 'Serdam',
                'fulfillment_method' => 'pickup',
                'order_status' => 'paid',
                'payment_status' => 'paid',
                'payment_method' => 'Credit / Debit Simulation',
                'delivery_fee' => 0,
                'created_at' => '2026-09-02 18:45:00',
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
            $customerEmail = $customer?->email ?? 'customer@buildnfix.test';
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
