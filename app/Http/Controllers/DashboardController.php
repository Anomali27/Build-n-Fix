<?php

namespace App\Http\Controllers;

use App\Repositories\BranchStockRepositories;
use App\Repositories\OrderRepositories;
use App\Repositories\PaymentRepositories;
use App\Repositories\ProductRepositories;
use App\Repositories\ReportRepositories;
use App\Repositories\SupplierRepositories;
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
        $totalProducts = count(ProductRepositories::getAll());
        $allOrders = OrderRepositories::all();
        $totalOrders = count($allOrders);
        $totalSuppliers = count(SupplierRepositories::getAll());

        // Stock Matrix summary
        $stockMatrix = BranchStockRepositories::getCategoryProductStockMatrix();
        $lowStockItems = array_filter($stockMatrix, function ($item) {
            return $item['stock_serdam'] <= 10 || $item['stock_gajahmada'] <= 10 || $item['stock_kotabaru'] <= 10;
        });

        // Sales report
        $salesReport = ReportRepositories::getSalesReport('all', 'this_month');

        // Customer specific
        $userId = (int) ($user['id'] ?? $user['user_id'] ?? 1);
        $customerOrders = OrderRepositories::getByUser($userId);
        $customerPayments = PaymentRepositories::getByUser($userId);

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
