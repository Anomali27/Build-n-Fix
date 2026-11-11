<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $authUser = $request->user();
        $user = $authUser ? [
            'id' => $authUser->id,
            'user_id' => $authUser->id,
            'name' => $authUser->name,
            'email' => $authUser->email,
            'role' => $authUser->role ?? 'customer',
            'branch' => $authUser->branch,
        ] : [
            'name' => 'Customer',
            'role' => 'customer',
            'branch' => null,
        ];

        $role = $user['role'] ?? 'customer';
        $branch = $user['branch'] ?? 'Serdam';

        $totalProducts = Product::query()->active()->count();
        $totalOrders = Order::query()->count();
        $totalSuppliers = Supplier::query()->count();
        $branchCount = Branch::query()->open()->count();

        $recentOrders = Order::query()
            ->with(['branch', 'items', 'latestPayment'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(fn (Order $order): array => $order->toDisplayArray())
            ->all();

        $lowStockProducts = Product::query()
            ->with(['category', 'branchStocks'])
            ->whereHas('branchStocks', fn ($q) => $q->where('stock', '<=', 10))
            ->get()
            ->map(function (Product $product): array {
                $totalStock = (int) $product->branchStocks->sum('stock');

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category_name' => $product->category?->name ?? 'Material Bangunan',
                    'unit' => $product->unit ?? $product->size ?? 'Unit',
                    'total_stock' => $totalStock,
                    'min_branch_stock' => (int) ($product->branchStocks->min('stock') ?? 0),
                ];
            })
            ->sortBy('min_branch_stock')
            ->values();

        $lowStockCount = $lowStockProducts->count();
        $lowStockItems = $lowStockProducts->take(5)->all();

        $salesReport = $role === 'owner'
            ? Order::buildSalesReport('all', 'this_year')
            : [];

        $customerOrders = $authUser
            ? Order::query()
                ->with(['branch', 'items', 'latestPayment'])
                ->forUser((int) $authUser->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn (Order $order): array => $order->toDisplayArray())
                ->all()
            : [];

        $customerPayments = $authUser
            ? Payment::query()
                ->with(['order', 'branch'])
                ->forUser((int) $authUser->id)
                ->orderByDesc('paid_at')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('dashboard.index', [
            'role' => $role,
            'user' => $user,
            'branch' => $branch,
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalSuppliers' => $totalSuppliers,
            'branchCount' => $branchCount,
            'recentOrders' => $recentOrders,
            'lowStockItems' => $lowStockItems,
            'lowStockCount' => $lowStockCount,
            'salesReport' => $salesReport,
            'customerOrders' => $customerOrders,
            'customerPayments' => $customerPayments,
        ]);
    }
}
