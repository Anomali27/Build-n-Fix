<?php

namespace App\Http\Controllers;

use App\Repositories\PaymentRepositories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $role = session('user.role', 'customer');
        $sessionUser = Session::get('user');

        if (! $sessionUser && ! Auth::check()) {
            return redirect()->route('login')->with('info', 'Silakan login terlebih dahulu untuk melihat riwayat pembayaran.');
        }

        $userId = (int) ($sessionUser['user_id'] ?? $sessionUser['id'] ?? (Auth::id() ?? 1));
        $statusFilter = $request->get('status', 'all');
        $branchFilter = $request->get('branch', 'all');
        $search = $request->get('q', '');

        if ($role === 'customer') {
            $payments = PaymentRepositories::getByUser($userId);
        } else {
            $payments = PaymentRepositories::getAll();
        }

        if ($branchFilter !== 'all') {
            $payments = array_filter($payments, function ($p) use ($branchFilter) {
                return strtolower($p['branch_name'] ?? '') === strtolower($branchFilter);
            });
        }

        if ($statusFilter !== 'all') {
            $payments = array_filter($payments, function ($p) use ($statusFilter) {
                return ($p['status'] ?? '') === $statusFilter;
            });
        }

        if (! empty($search)) {
            $q = strtolower(trim($search));
            $payments = array_filter($payments, function ($p) use ($q) {
                return str_contains(strtolower($p['payment_code'] ?? ''), $q) ||
                       str_contains(strtolower($p['order_number'] ?? ''), $q) ||
                       str_contains(strtolower($p['customer_name'] ?? ''), $q) ||
                       str_contains(strtolower($p['payment_method'] ?? ''), $q) ||
                       str_contains(strtolower($p['branch_name'] ?? ''), $q);
            });
        }

        // Summary stats
        $allPayments = $role === 'customer' ? PaymentRepositories::getByUser($userId) : PaymentRepositories::getAll();
        $totalPaidAmount = 0;
        $pendingCount = 0;
        $paidCount = 0;
        foreach ($allPayments as $p) {
            if (($p['status'] ?? '') === 'paid') {
                $totalPaidAmount += (int) ($p['amount'] ?? 0);
                $paidCount++;
            } elseif (($p['status'] ?? '') === 'pending') {
                $pendingCount++;
            }
        }

        return view('payments.index', [
            'role' => $role,
            'payments' => array_values($payments),
            'statusFilter' => $statusFilter,
            'branchFilter' => $branchFilter,
            'search' => $search,
            'totalPaidAmount' => $totalPaidAmount,
            'pendingCount' => $pendingCount,
            'paidCount' => $paidCount,
            'totalTransactions' => count($allPayments),
        ]);
    }

    public function show(string|int $id)
    {
        $role = session('user.role', 'customer');
        $payment = PaymentRepositories::find($id);

        if (! $payment) {
            return redirect()->route('payments.index')->with('error', 'Data pembayaran tidak ditemukan.');
        }

        return view('payments.index', [
            'role' => $role,
            'payments' => PaymentRepositories::getAll(),
            'selectedPayment' => $payment,
            'statusFilter' => 'all',
            'branchFilter' => 'all',
            'search' => '',
            'totalPaidAmount' => 0,
            'pendingCount' => 0,
            'paidCount' => 0,
            'totalTransactions' => 0,
        ]);
    }

    public function update(Request $request, string|int $id)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('payments.index')->with('error', 'Hanya Admin yang dapat memverifikasi pembayaran.');
        }

        $status = $request->input('status', 'paid');
        PaymentRepositories::updateStatus($id, $status);

        return redirect()->route('payments.index')->with('success', 'Status pembayaran berhasil diperbarui menjadi '.ucfirst($status).'.');
    }
}
