<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Session;

class OrderRepositories
{
    /**
     * Centralized mock order data.
     *
     * @var array<int, array<string, mixed>>
     */
    protected static array $orders = [
        [
            'id' => 1,
            'order_number' => 'BNF-20260902-001',
            'user_id' => 1,
            'branch_id' => 1,
            'branch_name' => 'Serdam',
            'branch_address' => 'Jl. Sungai Raya Dalam No. 88, Pontianak',
            'fulfillment_method' => 'pickup',
            'payment_status' => 'paid',
            'payment_method' => 'Mock Payment Gateway',
            'order_status' => 'ready_to_pick_up',
            'customer_name' => 'Customer',
            'customer_email' => 'customer@buildnfix.test',
            'customer_phone' => '081234567890',
            'delivery_address' => null,
            'subtotal' => 130000,
            'delivery_fee' => 0,
            'total' => 130000,
            'created_at' => '2026-09-02 10:30:00',
            'items' => [
                [
                    'product_id' => 1,
                    'sku' => 'SMN-001',
                    'product_name' => 'Semen Portland 40 Kg',
                    'brand' => 'Semen Indonesia',
                    'image' => 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=1200&auto=format&fit=crop&q=80',
                    'quantity' => 2,
                    'unit_price' => 65000,
                    'subtotal' => 130000,
                ],
            ],
        ],
        [
            'id' => 2,
            'order_number' => 'BNF-20260902-002',
            'user_id' => 1,
            'branch_id' => 2,
            'branch_name' => 'Gajahmada',
            'branch_address' => 'Jl. Gajah Mada No. 102, Pontianak',
            'fulfillment_method' => 'delivery',
            'payment_status' => 'paid',
            'payment_method' => 'Mock Payment Gateway',
            'order_status' => 'on_delivery',
            'customer_name' => 'Customer',
            'customer_email' => 'customer@buildnfix.test',
            'customer_phone' => '081234567890',
            'delivery_address' => 'Jl. Gajah Mada No. 45, Pontianak',
            'subtotal' => 230000,
            'delivery_fee' => 15000,
            'total' => 245000,
            'created_at' => '2026-09-02 14:15:00',
            'items' => [
                [
                    'product_id' => 5,
                    'sku' => 'MTR-001',
                    'product_name' => 'Mortar Instan 40 Kg',
                    'brand' => 'Mortar Utama',
                    'image' => 'https://images.unsplash.com/photo-1541888946425-d0fbb186a5b7?w=1200&auto=format&fit=crop&q=80',
                    'quantity' => 2,
                    'unit_price' => 115000,
                    'subtotal' => 230000,
                ],
            ],
        ],
        [
            'id' => 3,
            'order_number' => 'BNF-20260828-003',
            'user_id' => 1,
            'branch_id' => 3,
            'branch_name' => 'Kota Baru',
            'branch_address' => 'Jl. Kota Baru No. 15, Pontianak',
            'fulfillment_method' => 'delivery',
            'payment_status' => 'paid',
            'payment_method' => 'Mock Payment Gateway',
            'order_status' => 'order_completed',
            'customer_name' => 'Customer',
            'customer_email' => 'customer@buildnfix.test',
            'customer_phone' => '081234567890',
            'delivery_address' => 'Jl. Perdana No. 88, Pontianak',
            'subtotal' => 325000,
            'delivery_fee' => 20000,
            'total' => 345000,
            'created_at' => '2026-08-28 09:00:00',
            'items' => [
                [
                    'product_id' => 1,
                    'sku' => 'SMN-001',
                    'product_name' => 'Semen Portland 40 Kg',
                    'brand' => 'Semen Indonesia',
                    'image' => 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=1200&auto=format&fit=crop&q=80',
                    'quantity' => 5,
                    'unit_price' => 65000,
                    'subtotal' => 325000,
                ],
            ],
        ],
        [
            'id' => 4,
            'order_number' => 'BNF-20260902-004',
            'user_id' => 1,
            'branch_id' => 1,
            'branch_name' => 'Serdam',
            'branch_address' => 'Jl. Sungai Raya Dalam No. 88, Pontianak',
            'fulfillment_method' => 'pickup',
            'payment_status' => 'paid',
            'payment_method' => 'Mock Payment Gateway',
            'order_status' => 'paid',
            'customer_name' => 'Customer',
            'customer_email' => 'customer@buildnfix.test',
            'customer_phone' => '081234567890',
            'delivery_address' => null,
            'subtotal' => 95000,
            'delivery_fee' => 0,
            'total' => 95000,
            'created_at' => '2026-09-02 18:45:00',
            'items' => [
                [
                    'product_id' => 4,
                    'sku' => 'SMN-004',
                    'product_name' => 'Semen Putih 20 Kg',
                    'brand' => 'Tiga Roda',
                    'image' => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=1200&auto=format&fit=crop&q=80',
                    'quantity' => 1,
                    'unit_price' => 95000,
                    'subtotal' => 95000,
                ],
            ],
        ],
    ];

    /**
     * Get all orders (static mock + session orders).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $sessionOrders = Session::get('session_orders', []);

        return array_merge(static::$orders, $sessionOrders);
    }

    /**
     * Get orders for specific user ID.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getByUser(int $userId): array
    {
        $userOrders = array_filter(static::all(), function ($order) use ($userId) {
            return (int) ($order['user_id'] ?? 0) === $userId;
        });

        // Sort newest to oldest
        usort($userOrders, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return array_values($userOrders);
    }

    /**
     * Get orders for specific branch name.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getByBranch(string $branchName): array
    {
        if (strtolower($branchName) === 'all') {
            return static::all();
        }

        $branchOrders = array_filter(static::all(), function ($order) use ($branchName) {
            return strtolower($order['branch_name'] ?? '') === strtolower($branchName);
        });

        usort($branchOrders, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return array_values($branchOrders);
    }

    /**
     * Find order by order number or ID.
     *
     * @return array<string, mixed>|null
     */
    public static function find(string $identifier): ?array
    {
        foreach (static::all() as $order) {
            if ($order['order_number'] === $identifier || (string) $order['id'] === (string) $identifier) {
                return $order;
            }
        }

        return null;
    }

    /**
     * Save a new order to session storage.
     *
     * @param  array<string, mixed>  $orderData
     * @return array<string, mixed>
     */
    public static function create(array $orderData): array
    {
        $allOrders = static::all();
        $newId = count($allOrders) + 1;

        $dateStr = date('Ymd');
        $orderNumber = 'BNF-'.$dateStr.'-'.str_pad((string) $newId, 3, '0', STR_PAD_LEFT);

        $newOrder = array_merge([
            'id' => $newId,
            'order_number' => $orderNumber,
            'user_id' => 1,
            'branch_id' => 1,
            'branch_name' => 'Serdam',
            'branch_address' => 'Jl. Sungai Raya Dalam No. 88, Pontianak',
            'fulfillment_method' => 'pickup',
            'payment_status' => 'paid',
            'payment_method' => 'Mock Payment Gateway',
            'order_status' => 'paid',
            'customer_name' => 'Customer',
            'customer_email' => 'customer@buildnfix.test',
            'customer_phone' => '081234567890',
            'delivery_address' => null,
            'subtotal' => 0,
            'delivery_fee' => 0,
            'total' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'items' => [],
        ], $orderData);

        $sessionOrders = Session::get('session_orders', []);
        $sessionOrders[] = $newOrder;
        Session::put('session_orders', $sessionOrders);

        return $newOrder;
    }
}
