<?php

namespace App\Http\Controllers;

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
     * Display the Order Center page.
     */
    public function index(Request $request)
    {
        $sessionUser = Session::get('user');

        if (! $sessionUser && ! Auth::check()) {
            return redirect()->route('login')->with('info', 'Silakan login terlebih dahulu untuk melihat pesanan Anda.');
        }

        $userId = (int) ($sessionUser['user_id'] ?? (Auth::id() ?? 1));
        $tab = $request->query('tab', 'all');
        $filter = $request->query('filter', 'all');
        $search = $request->query('search');
        $selectedOrderNumber = $request->query('order');

        $allOrders = $this->orderService->getCustomerOrders($userId, $filter, $search);
        $successOrders = $this->orderService->getSuccessOrders($userId);
        $trackingOrders = $this->orderService->getTrackingOrders($userId);
        $historyOrders = $this->orderService->getHistoryOrders($userId);

        $selectedOrder = null;
        if ($selectedOrderNumber) {
            $selectedOrder = $this->orderService->getOrderDetail($selectedOrderNumber, $userId);
        }

        return view('orders.index', [
            'activeTab' => $tab,
            'activeFilter' => $filter,
            'search' => $search,
            'allOrders' => $allOrders,
            'successOrders' => $successOrders,
            'trackingOrders' => $trackingOrders,
            'historyOrders' => $historyOrders,
            'selectedOrder' => $selectedOrder,
        ]);
    }

    /**
     * Display single order detail inside Order Center.
     */
    public function show(string $orderNumber)
    {
        $sessionUser = Session::get('user');

        if (! $sessionUser && ! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = (int) ($sessionUser['user_id'] ?? (Auth::id() ?? 1));
        $order = $this->orderService->getOrderDetail($orderNumber, $userId);

        if (! $order) {
            abort(404, 'Pesanan tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $allOrders = $this->orderService->getCustomerOrders($userId);
        $successOrders = $this->orderService->getSuccessOrders($userId);
        $trackingOrders = $this->orderService->getTrackingOrders($userId);
        $historyOrders = $this->orderService->getHistoryOrders($userId);

        return view('orders.index', [
            'activeTab' => 'all',
            'activeFilter' => 'all',
            'search' => null,
            'allOrders' => $allOrders,
            'successOrders' => $successOrders,
            'trackingOrders' => $trackingOrders,
            'historyOrders' => $historyOrders,
            'selectedOrder' => $order,
        ]);
    }
}
