<?php

namespace App\Services;

use App\Data\OrderData;
use Illuminate\Support\Facades\Session;

class OrderService
{
    /**
     * Get all orders for a customer with optional filtering.
     *
     * @param  string  $filter  'all', 'pickup', 'delivery', 'active', 'completed'
     * @return array<int, array<string, mixed>>
     */
    public function getCustomerOrders(int $userId, string $filter = 'all', ?string $search = null): array
    {
        $orders = OrderData::getByUser($userId);

        if (! empty($search)) {
            $query = strtolower(trim($search));
            $orders = array_filter($orders, function ($order) use ($query) {
                return str_contains(strtolower($order['order_number']), $query)
                    || str_contains(strtolower($order['branch_name']), $query);
            });
        }

        switch (strtolower($filter)) {
            case 'pickup':
                $orders = array_filter($orders, fn ($o) => ($o['fulfillment_method'] ?? '') === 'pickup');
                break;
            case 'delivery':
                $orders = array_filter($orders, fn ($o) => ($o['fulfillment_method'] ?? '') === 'delivery');
                break;
            case 'active':
                $orders = array_filter($orders, fn ($o) => ($o['order_status'] ?? '') !== 'order_completed');
                break;
            case 'completed':
                $orders = array_filter($orders, fn ($o) => ($o['order_status'] ?? '') === 'order_completed');
                break;
        }

        return array_values($orders);
    }

    /**
     * Get successful orders (payment_status === 'paid').
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSuccessOrders(int $userId): array
    {
        $orders = OrderData::getByUser($userId);
        $successOrders = array_filter($orders, function ($o) {
            return ($o['payment_status'] ?? '') === 'paid';
        });

        return array_values($successOrders);
    }

    /**
     * Get active/in-progress orders for tracking tab.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTrackingOrders(int $userId): array
    {
        $orders = OrderData::getByUser($userId);
        $activeOrders = array_filter($orders, function ($o) {
            return ($o['order_status'] ?? '') !== 'order_completed';
        });

        return array_values($activeOrders);
    }

    /**
     * Get completed orders for history tab.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getHistoryOrders(int $userId): array
    {
        $orders = OrderData::getByUser($userId);
        $historyOrders = array_filter($orders, function ($o) {
            return ($o['order_status'] ?? '') === 'order_completed';
        });

        return array_values($historyOrders);
    }

    /**
     * Get detail of a specific order, checking customer authorization.
     *
     * @return array<string, mixed>|null
     */
    public function getOrderDetail(string $identifier, int $userId): ?array
    {
        $order = OrderData::find($identifier);

        if (! $order) {
            return null;
        }

        // Customer access control check
        if ((int) ($order['user_id'] ?? 0) !== $userId) {
            return null;
        }

        $order['timeline'] = $this->getTrackingTimeline($order);
        $order['status_label'] = $this->getStatusLabel($order['order_status'] ?? 'paid');

        return $order;
    }

    /**
     * Determine tracking timeline steps based on fulfillment method and order status.
     *
     * @param  array<string, mixed>  $order
     * @return array<int, array{key: string, label: string, description: string, completed: bool, current: bool}>
     */
    public function getTrackingTimeline(array $order): array
    {
        $method = strtolower($order['fulfillment_method'] ?? 'pickup');
        $currentStatus = strtolower($order['order_status'] ?? 'paid');

        if ($method === 'pickup') {
            $steps = [
                [
                    'key' => 'paid',
                    'label' => 'Payment Confirmed',
                    'description' => 'Pembayaran berhasil dan pesanan sedang diproses.',
                ],
                [
                    'key' => 'ready_to_pick_up',
                    'label' => 'Ready to Pick Up',
                    'description' => 'Pesanan sudah siap diambil di cabang '.($order['branch_name'] ?? 'Serdam').'.',
                ],
                [
                    'key' => 'order_completed',
                    'label' => 'Order Completed',
                    'description' => 'Pesanan telah diambil dan selesai.',
                ],
            ];
            $statusOrder = ['paid', 'ready_to_pick_up', 'order_completed'];
        } else {
            $steps = [
                [
                    'key' => 'paid',
                    'label' => 'Payment Confirmed',
                    'description' => 'Pembayaran berhasil dan pesanan sedang diproses.',
                ],
                [
                    'key' => 'proses',
                    'label' => 'Order Processing',
                    'description' => 'Pesanan sedang dipersiapkan untuk pengiriman.',
                ],
                [
                    'key' => 'on_delivery',
                    'label' => 'On Delivery',
                    'description' => 'Pesanan sedang dalam perjalanan ke alamat tujuan.',
                ],
                [
                    'key' => 'order_completed',
                    'label' => 'Order Completed',
                    'description' => 'Pesanan telah diterima dan selesai.',
                ],
            ];
            $statusOrder = ['paid', 'proses', 'on_delivery', 'order_completed'];
        }

        $currentIndex = array_search($currentStatus, $statusOrder);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        foreach ($steps as $index => &$step) {
            $step['completed'] = $index <= $currentIndex;
            $step['current'] = $index === $currentIndex;
        }

        return $steps;
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabel(string $status): string
    {
        return match (strtolower($status)) {
            'paid' => 'Paid',
            'ready_to_pick_up' => 'Ready to Pick Up',
            'proses' => 'Proses',
            'on_delivery' => 'On Delivery',
            'order_completed' => 'Order Completed',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Create an order from Checkout session after successful payment simulation.
     *
     * @param  array<string, mixed>  $checkoutData
     * @return array<string, mixed>
     */
    public function createOrderFromCheckout(array $checkoutData, string $paymentReference, string $paymentMethod): array
    {
        $cart = $checkoutData['cart'];
        $items = [];

        foreach ($cart['items'] as $item) {
            $items[] = [
                'product_id' => $item['product_id'],
                'sku' => $item['product']['sku'] ?? 'SKU-'.$item['product_id'],
                'product_name' => $item['product']['name'] ?? 'Product Item',
                'brand' => $item['product']['brand'] ?? 'Build n Fix',
                'image' => $item['product']['image'] ?? '',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
            ];
        }

        $userSession = Session::get('user', []);
        $userId = (int) ($userSession['user_id'] ?? 1);

        $fulfillmentMethod = strtolower($checkoutData['fulfillment_method'] ?? 'pickup');

        // Order status rule:
        // Pickup -> ready_to_pick_up
        // Delivery -> proses
        $orderStatus = $fulfillmentMethod === 'pickup' ? 'ready_to_pick_up' : 'proses';

        $formattedAddress = null;
        if ($fulfillmentMethod === 'delivery' && ! empty($checkoutData['delivery_address'])) {
            $addr = $checkoutData['delivery_address'];
            $formattedAddress = implode(', ', array_filter([
                $addr['address'] ?? '',
                $addr['city'] ?? '',
                $addr['postal_code'] ?? '',
            ]));
        }

        $orderPayload = [
            'user_id' => $userId,
            'branch_id' => $checkoutData['branch']['id'],
            'branch_name' => $checkoutData['branch']['name'],
            'branch_address' => 'Jl. '.$checkoutData['branch']['name'].' No. 88, Pontianak',
            'fulfillment_method' => $fulfillmentMethod,
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
            'order_status' => $orderStatus,
            'customer_name' => $checkoutData['customer']['name'],
            'customer_email' => $checkoutData['customer']['email'],
            'customer_phone' => $checkoutData['customer']['phone'],
            'delivery_address' => $formattedAddress,
            'subtotal' => $checkoutData['subtotal'],
            'delivery_fee' => $checkoutData['delivery_fee'],
            'total' => $checkoutData['total'],
            'items' => $items,
        ];

        return OrderData::create($orderPayload);
    }
}
