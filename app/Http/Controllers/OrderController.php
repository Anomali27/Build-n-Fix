<?php

namespace App\Http\Controllers;

use App\Repositories\OrderRepositories;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display the Orders page (Management Table for Admin/Owner, Order Center for Customer).
     */
    public function index(Request $request)
    {
        $role = session('user.role', 'customer');
        $sessionUser = Session::get('user');

        if (! $sessionUser && ! Auth::check()) {
            return redirect()->route('login')->with('info', 'Silakan login terlebih dahulu untuk mengakses pesanan.');
        }

        $userId = (int) ($sessionUser['user_id'] ?? $sessionUser['id'] ?? (Auth::id() ?? 1));
        $tab = $request->query('tab', 'all');
        $filter = $request->query('filter', 'all');
        $search = $request->query('search', $request->query('q', ''));
        $branchFilter = $request->query('branch', 'all');
        $statusFilter = $request->query('status', 'all');
        $selectedOrderNumber = $request->query('order');

        // Customer datasets
        $allOrders = $this->orderService->getCustomerOrders($userId, $filter, $search);
        $successOrders = $this->orderService->getSuccessOrders($userId);
        $trackingOrders = $this->orderService->getTrackingOrders($userId);
        $historyOrders = $this->orderService->getHistoryOrders($userId);

        $selectedOrder = null;
        if ($selectedOrderNumber) {
            $selectedOrder = $this->orderService->getOrderDetail($selectedOrderNumber, $role === 'customer' ? $userId : null);
        }

        // Admin & Owner Management dataset
        $managementOrders = OrderRepositories::all();

        if ($branchFilter !== 'all') {
            $managementOrders = array_filter($managementOrders, function ($o) use ($branchFilter) {
                return strtolower($o['branch_name'] ?? '') === strtolower($branchFilter);
            });
        }

        if ($statusFilter !== 'all') {
            $managementOrders = array_filter($managementOrders, function ($o) use ($statusFilter) {
                return ($o['order_status'] ?? '') === $statusFilter;
            });
        }

        if (! empty($search)) {
            $q = strtolower(trim($search));
            $managementOrders = array_filter($managementOrders, function ($o) use ($q) {
                return str_contains(strtolower($o['order_number'] ?? ''), $q) ||
                       str_contains(strtolower($o['customer_name'] ?? ''), $q) ||
                       str_contains(strtolower($o['customer_email'] ?? ''), $q) ||
                       str_contains(strtolower($o['payment_method'] ?? ''), $q) ||
                       str_contains(strtolower($o['branch_name'] ?? ''), $q);
            });
        }

        return view('orders.index', [
            'role' => $role,
            'activeTab' => $tab,
            'activeFilter' => $filter,
            'search' => $search,
            'branchFilter' => $branchFilter,
            'statusFilter' => $statusFilter,
            'allOrders' => $allOrders,
            'successOrders' => $successOrders,
            'trackingOrders' => $trackingOrders,
            'historyOrders' => $historyOrders,
            'selectedOrder' => $selectedOrder,
            'managementOrders' => array_values($managementOrders),
        ]);
    }

    /**
     * Display single order detail.
     */
    public function show(string $orderNumber)
    {
        $role = session('user.role', 'customer');
        $sessionUser = Session::get('user');

        if (! $sessionUser && ! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = (int) ($sessionUser['user_id'] ?? $sessionUser['id'] ?? (Auth::id() ?? 1));
        $order = $this->orderService->getOrderDetail($orderNumber, $role === 'customer' ? $userId : null);

        if (! $order) {
            abort(404, 'Pesanan tidak ditemukan atau Anda tidak memiliki akses.');
        }

        if ($role === 'customer') {
            $allOrders = $this->orderService->getCustomerOrders($userId);
            $successOrders = $this->orderService->getSuccessOrders($userId);
            $trackingOrders = $this->orderService->getTrackingOrders($userId);
            $historyOrders = $this->orderService->getHistoryOrders($userId);

            return view('orders.index', [
                'role' => $role,
                'activeTab' => 'all',
                'activeFilter' => 'all',
                'search' => null,
                'allOrders' => $allOrders,
                'successOrders' => $successOrders,
                'trackingOrders' => $trackingOrders,
                'historyOrders' => $historyOrders,
                'selectedOrder' => $order,
                'managementOrders' => [],
            ]);
        }

        return view('orders.index', [
            'role' => $role,
            'activeTab' => 'all',
            'activeFilter' => 'all',
            'search' => null,
            'branchFilter' => 'all',
            'statusFilter' => 'all',
            'allOrders' => [],
            'successOrders' => [],
            'trackingOrders' => [],
            'historyOrders' => [],
            'selectedOrder' => $order,
            'managementOrders' => OrderRepositories::all(),
        ]);
    }

    /**
     * Update order status (Admin only).
     */
    public function update(Request $request, string $orderNumber)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('orders.index')->with('error', 'Hanya Admin yang dapat memperbarui status pesanan.');
        }

        $status = $request->input('order_status');
        $paymentStatus = $request->input('payment_status');

        OrderRepositories::updateStatus($orderNumber, $status, $paymentStatus);

        return redirect()->route('orders.index')->with('success', 'Status pesanan '.$orderNumber.' berhasil diperbarui.');
    }
}
