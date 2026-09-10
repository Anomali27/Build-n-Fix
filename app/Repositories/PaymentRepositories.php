<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Session;

class PaymentRepositories
{
    /**
     * Get all payment transactions derived from orders + session overrides.
     */
    public static function getAll(): array
    {
        $orders = OrderRepositories::all();
        $overrides = Session::get('payment_overrides', []);

        $payments = [];
        foreach ($orders as $order) {
            $paymentId = $order['id'] ?? 1;
            $status = $order['payment_status'] ?? 'paid';
            $method = $order['payment_method'] ?? 'Mock Payment Gateway';

            $payment = [
                'id' => $paymentId,
                'payment_code' => 'PAY-'.str_replace('BNF-', '', $order['order_number'] ?? ('ORD-'.$paymentId)),
                'order_id' => $order['id'] ?? $paymentId,
                'order_number' => $order['order_number'] ?? ('BNF-2026-'.str_pad((string) $paymentId, 3, '0', STR_PAD_LEFT)),
                'user_id' => $order['user_id'] ?? 1,
                'customer_name' => $order['customer_name'] ?? 'Customer',
                'customer_email' => $order['customer_email'] ?? 'customer@buildnfix.test',
                'branch_id' => $order['branch_id'] ?? 1,
                'branch_name' => $order['branch_name'] ?? 'Serdam',
                'payment_method' => $method,
                'amount' => (int) ($order['total'] ?? 0),
                'status' => $status, // paid, pending, failed, refunded
                'status_label' => match ($status) {
                    'paid' => 'Lunas (Verified)',
                    'pending' => 'Menunggu Pembayaran',
                    'failed' => 'Gagal',
                    'refunded' => 'Dikembalikan',
                    default => ucfirst($status),
                },
                'date' => $order['created_at'] ?? date('Y-m-d H:i:s'),
                'proof_url' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600&auto=format&fit=crop&q=80',
            ];

            if (isset($overrides[$paymentId])) {
                $payment = array_merge($payment, $overrides[$paymentId]);
            }

            $payments[] = $payment;
        }

        // Sort descending
        usort($payments, fn ($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

        return $payments;
    }

    public static function getByUser(int $userId): array
    {
        return array_values(array_filter(self::getAll(), fn ($p) => (int) ($p['user_id'] ?? 0) === $userId));
    }

    public static function getByBranch(string $branchName): array
    {
        if (strtolower($branchName) === 'all') {
            return self::getAll();
        }

        return array_values(array_filter(self::getAll(), function ($p) use ($branchName) {
            return strtolower($p['branch_name'] ?? '') === strtolower($branchName);
        }));
    }

    public static function find(int|string $id): ?array
    {
        foreach (self::getAll() as $p) {
            if ((string) $p['id'] === (string) $id ||
                ($p['payment_code'] ?? '') === (string) $id ||
                ($p['order_number'] ?? '') === (string) $id) {
                return $p;
            }
        }

        return null;
    }

    public static function updateStatus(int|string $id, string $status): bool
    {
        $payment = self::find($id);
        if (! $payment) {
            return false;
        }

        $paymentId = $payment['id'];
        $overrides = Session::get('payment_overrides', []);
        $overrides[$paymentId] = array_merge($payment, [
            'status' => $status,
            'status_label' => match ($status) {
                'paid' => 'Lunas (Verified)',
                'pending' => 'Menunggu Pembayaran',
                'failed' => 'Gagal',
                'refunded' => 'Dikembalikan',
                default => ucfirst($status),
            },
        ]);
        Session::put('payment_overrides', $overrides);

        return true;
    }
}
