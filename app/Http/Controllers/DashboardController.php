<?php

namespace App\Http\Controllers;

use App\Repositories\BranchStockRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ReportRepository;
use App\Repositories\SupplierRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Session::get('user', [
            'name' => 'Customer',
            'role' => 'customer',
            'branch' => null,
        ]);

        $role = $user['role'] ?? 'customer';
        $branch = $user['branch'] ?? 'Serdam';

        // Prepare data for each role
        $totalProducts = count(ProductRepository::getAll());
        $allOrders = OrderRepository::all();
        $totalOrders = count($allOrders);
        $totalSuppliers = count(SupplierRepository::getAll());

        // Stock Matrix summary
        $stockMatrix = BranchStockRepository::getCategoryProductStockMatrix();
        $lowStockItems = array_filter($stockMatrix, function ($item) {
            return $item['stock_serdam'] <= 10 || $item['stock_gajahmada'] <= 10 || $item['stock_kotabaru'] <= 10;
        });

        // Sales report
        $salesReport = ReportRepository::getSalesReport('all', 'this_month');

        // Customer specific
        $userId = (int) ($user['id'] ?? $user['user_id'] ?? 1);
        $customerOrders = OrderRepository::getByUser($userId);
        $customerPayments = PaymentRepository::getByUser($userId);

        return view('dashboard.index', [
            'role' => $role,
            'user' => $user,
            'branch' => $branch,
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalSuppliers' => $totalSuppliers,
            'recentOrders' => array_slice($allOrders, 0, 6),
            'lowStockItems' => array_slice($lowStockItems, 0, 5),
            'salesReport' => $salesReport,
            'customerOrders' => $customerOrders,
            'customerPayments' => $customerPayments,
        ]);
    }
}
