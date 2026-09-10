<?php

namespace App\Http\Controllers;

use App\Repositories\BranchStockRepositories;
use App\Repositories\CategoryRepositories;
use App\Repositories\ProductRepositories;
use App\Repositories\StockMovementRepositories;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $role = session('user.role', 'customer');
        $search = $request->get('q', '');
        $selectedCategory = $request->get('category', 'all');

        // Customer will see availability through products catalog
        if ($role === 'customer') {
            return redirect()->route('products.index')->with('info', 'Ketersediaan stok dapat dilihat langsung pada setiap kartu produk.');
        }

        $categories = CategoryRepositories::getAll();
        $stockMatrix = BranchStockRepositories::getCategoryProductStockMatrix($search, $selectedCategory);

        // Group matrix by category for beautiful categorized presentation
        $groupedStocks = [];
        foreach ($stockMatrix as $item) {
            $catName = $item['category_name'];
            if (! isset($groupedStocks[$catName])) {
                $groupedStocks[$catName] = [];
            }
            $groupedStocks[$catName][] = $item;
        }

        // Summary metrics
        $totalItems = count($stockMatrix);
        $lowStockCount = 0;
        $totalUnits = 0;
        foreach ($stockMatrix as $item) {
            $totalUnits += $item['total_stock'];
            if ($item['stock_serdam'] <= 10 || $item['stock_gajahmada'] <= 10 || $item['stock_kotabaru'] <= 10) {
                $lowStockCount++;
            }
        }

        return view('stock.index', [
            'role' => $role,
            'groupedStocks' => $groupedStocks,
            'stockMatrix' => $stockMatrix,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
            'totalItems' => $totalItems,
            'lowStockCount' => $lowStockCount,
            'totalUnits' => $totalUnits,
        ]);
    }

    public function adjust(Request $request)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('stock.index')->with('error', 'Hanya Admin yang dapat mengubah stok.');
        }

        $request->validate([
            'product_id' => 'required|numeric',
            'branch_id' => 'required|in:1,2,3',
            'action_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $productId = (int) $request->input('product_id');
        $branchId = (int) $request->input('branch_id');
        $actionType = $request->input('action_type');
        $qty = (int) $request->input('quantity');
        $notes = $request->input('notes') ?: 'Penyesuaian stok oleh Admin';

        $branchNames = [1 => 'Serdam', 2 => 'Gajahmada', 3 => 'Kota Baru'];
        $branchName = $branchNames[$branchId] ?? 'Serdam';

        $product = ProductRepositories::find($productId);
        $productName = $product['name'] ?? 'Produk';
        $productSku = $product['sku'] ?? 'SKU-000';
        $categoryName = $product['category'] ?? 'Umum';

        $previousStock = BranchStockRepositories::getStock($productId, $branchId);
        $newStock = $previousStock;
        $movementType = 'adjustment';
        $movementQty = $qty;

        if ($actionType === 'add') {
            $newStock = $previousStock + $qty;
            $movementType = 'in';
            $movementQty = $qty;
        } elseif ($actionType === 'subtract') {
            $newStock = max(0, $previousStock - $qty);
            $movementType = 'out';
            $movementQty = $qty;
        } elseif ($actionType === 'set') {
            $newStock = max(0, $qty);
            $movementType = 'adjustment';
            $movementQty = $newStock - $previousStock;
        }

        BranchStockRepositories::setStock($productId, $branchId, $newStock);

        // Record stock movement log
        StockMovementRepositories::record([
            'product_id' => $productId,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'category_name' => $categoryName,
            'branch_id' => $branchId,
            'branch_name' => $branchName,
            'type' => $movementType,
            'quantity' => $movementQty,
            'previous_stock' => $previousStock,
            'current_stock' => $newStock,
            'source' => 'Penyesuaian Manual Admin',
            'notes' => $notes,
            'admin_name' => session('user.name') ?? 'Admin ' . $branchName,
        ]);

        return redirect()->route('stock.index')->with('success', 'Stok '.$productName.' di Cabang '.$branchName.' berhasil diperbarui (Stok: '.$newStock.').');
    }
}
