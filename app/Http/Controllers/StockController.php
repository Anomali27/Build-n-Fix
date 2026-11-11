<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stock\AdjustStockRequest;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';
        $search = (string) $request->get('q', '');
        $selectedCategory = (string) $request->get('category', 'all');

        if ($role === 'customer') {
            return redirect()->route('products.index')->with('info', 'Ketersediaan stok dapat dilihat langsung pada setiap kartu produk.');
        }

        $categories = Category::orderBy('name')->get();
        $branches = Branch::orderBy('id')->get();

        $products = Product::query()
            ->with(['category', 'branchStocks'])
            ->search($search !== '' ? $search : null)
            ->forCategory($selectedCategory !== 'all' ? $selectedCategory : null)
            ->orderBy('name')
            ->get()
            ->sortBy(fn (Product $p): string => ($p->category?->name ?? 'Lainnya').'|'.$p->name)
            ->values();

        $stockMatrix = [];
        $groupedStocks = [];
        $lowStockCount = 0;
        $totalUnits = 0;

        foreach ($products as $product) {
            $catName = $product->category?->name ?? 'Lainnya';
            $stocksByBranch = $product->branchStocks->keyBy('branch_id');

            $branchRows = [];
            $productTotalStock = 0;
            $hasLowStock = false;

            foreach ($branches as $branch) {
                /** @var BranchStock|null $bs */
                $bs = $stocksByBranch->get($branch->id);
                $qty = $bs ? $bs->stock : 0;
                $price = $bs && $bs->price > 0 ? $bs->price : $product->price;

                $productTotalStock += $qty;
                if ($qty <= 10) {
                    $hasLowStock = true;
                }

                $branchRows[$branch->id] = [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'stock' => $qty,
                    'price' => $price,
                ];
            }

            $totalUnits += $productTotalStock;
            if ($hasLowStock) {
                $lowStockCount++;
            }

            $item = [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'slug' => $product->slug,
                'category_id' => $product->category_id,
                'category_name' => $catName,
                'brand' => $product->brand ?? 'Build n Fix',
                'unit' => $product->unit ?? $product->size ?? 'Unit',
                'price' => $product->price,
                'image' => $product->image ?: 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600&auto=format&fit=crop&q=80',
                'branches' => array_values($branchRows),
                'total_stock' => $productTotalStock,
            ];

            $stockMatrix[] = $item;
            $groupedStocks[$catName][] = $item;
        }

        ksort($groupedStocks);

        return view('stock.index', [
            'role' => $role,
            'groupedStocks' => $groupedStocks,
            'stockMatrix' => $stockMatrix,
            'categories' => $categories,
            'branches' => $branches,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
            'totalItems' => count($stockMatrix),
            'lowStockCount' => $lowStockCount,
            'totalUnits' => $totalUnits,
        ]);
    }

    public function adjust(AdjustStockRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $productId = (int) $validated['product_id'];
        $branchId = (int) $validated['branch_id'];
        $actionType = $validated['action_type'];
        $qty = (int) $validated['quantity'];
        $notes = ($validated['notes'] ?? '') !== '' ? $validated['notes'] : 'Penyesuaian stok oleh Admin';

        $product = Product::with('category')->findOrFail($productId);
        $branch = Branch::findOrFail($branchId);
        $userId = auth()->id();
        $actorName = auth()->user()?->name ?? 'Admin '.$branch->name;

        $newStock = DB::transaction(function () use ($product, $branch, $actionType, $qty, $notes, $userId, $actorName): int {
            $branchStock = BranchStock::query()
                ->where('product_id', $product->id)
                ->where('branch_id', $branch->id)
                ->lockForUpdate()
                ->first();

            if (! $branchStock) {
                $branchStock = BranchStock::create([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                    'price' => $product->price,
                    'stock' => 0,
                ]);
            }

            $prev = $branchStock->stock;
            $next = $prev;
            $type = 'adjustment';
            $delta = $qty;

            if ($actionType === 'add') {
                $next = $prev + $qty;
                $type = 'in';
                $delta = $qty;
            } elseif ($actionType === 'subtract') {
                $next = max(0, $prev - $qty);
                $type = 'out';
                $delta = $prev - $next;
            } elseif ($actionType === 'set') {
                $next = max(0, $qty);
                $type = 'adjustment';
                $delta = $next - $prev;
            }

            $branchStock->update(['stock' => $next]);

            $movement = StockMovement::create([
                'reference_no' => 'TMP-'.Str::uuid()->toString(),
                'moved_at' => now(),
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'user_id' => $userId,
                'actor_name' => $actorName,
                'type' => $type,
                'quantity' => $delta,
                'previous_stock' => $prev,
                'current_stock' => $next,
                'source' => 'Penyesuaian Manual Admin',
                'notes' => $notes,
            ]);

            $movement->update([
                'reference_no' => 'MOV-'.now()->format('Y').'-'.str_pad((string) $movement->id, 3, '0', STR_PAD_LEFT),
            ]);

            return $next;
        });

        return redirect()->route('stock.index')
            ->with('success', 'Stok '.$product->name.' di Cabang '.$branch->name.' berhasil diperbarui (Stok: '.$newStock.').');
    }
}
