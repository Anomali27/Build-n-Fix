<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->buildCartPayload((int) $request->user()->id);

        return view('cart.index', [
            'cart' => $cart,
        ]);
    }

    public function store(StoreCartItemRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $userId = (int) $request->user()->id;
        $product = Product::findOrFail((int) $validated['product_id']);
        $quantity = (int) ($validated['quantity'] ?? 1);
        $branch = $this->resolveBranch($validated['branch'] ?? null);

        if (! $branch) {
            return $this->errorResponse($request, 'Cabang tidak ditemukan.', 404);
        }

        $branchStock = BranchStock::query()
            ->where('product_id', $product->id)
            ->where('branch_id', $branch->id)
            ->first();

        $availableStock = $branchStock ? $branchStock->stock : 0;

        $existing = CartItem::query()
            ->where('user_id', $userId)
            ->where('branch_id', $branch->id)
            ->where('product_id', $product->id)
            ->first();

        $newQty = ($existing?->quantity ?? 0) + $quantity;

        if ($availableStock <= 0 || $newQty > $availableStock) {
            return $this->errorResponse(
                $request,
                'Jumlah melebihi stok tersedia di Cabang '.$branch->name.' (stok: '.$availableStock.').',
                422
            );
        }

        $fulfillmentMethod = $validated['fulfillment_method']
            ?? $existing?->fulfillment_method
            ?? 'pickup';

        CartItem::updateOrCreate(
            [
                'user_id' => $userId,
                'branch_id' => $branch->id,
                'product_id' => $product->id,
            ],
            [
                'quantity' => $newQty,
                'fulfillment_method' => $fulfillmentMethod,
            ]
        );

        $cart = $this->buildCartPayload($userId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang Cabang '.$branch->name.'.',
                'cart' => $cart,
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Produk berhasil ditambahkan ke keranjang Cabang '.$branch->name.'.');
    }

    public function update(UpdateCartItemRequest $request, int|string $cartItem): JsonResponse|RedirectResponse
    {
        $userId = (int) $request->user()->id;
        $validated = $request->validated();

        $item = $this->resolveUserCartItem(
            $userId,
            $cartItem,
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null
        );

        if (! $item) {
            return $this->errorResponse($request, 'Item tidak ditemukan di keranjang.', 404);
        }

        if (array_key_exists('quantity', $validated) && $validated['quantity'] !== null) {
            $quantity = (int) $validated['quantity'];

            if ($quantity <= 0) {
                $item->delete();

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Item dihapus dari keranjang.',
                        'cart' => $this->buildCartPayload($userId),
                    ]);
                }

                return redirect()->route('cart.index')->with('success', 'Item berhasil dihapus dari keranjang.');
            }

            $availableStock = (int) (BranchStock::query()
                ->where('product_id', $item->product_id)
                ->where('branch_id', $item->branch_id)
                ->value('stock') ?? 0);

            if ($quantity > $availableStock) {
                return $this->errorResponse(
                    $request,
                    'Jumlah melebihi stok tersedia (tersisa '.$availableStock.').',
                    422
                );
            }

            $item->quantity = $quantity;
        }

        if (! empty($validated['fulfillment_method'])) {
            $item->fulfillment_method = $validated['fulfillment_method'];
        }

        $item->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Keranjang berhasil diperbarui.',
                'cart' => $this->buildCartPayload($userId),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Item keranjang berhasil diperbarui.');
    }

    public function destroy(Request $request, int|string $cartItem): JsonResponse|RedirectResponse
    {
        $userId = (int) $request->user()->id;
        $item = $this->resolveUserCartItem(
            $userId,
            $cartItem,
            $request->filled('branch_id') ? (int) $request->input('branch_id') : null
        );

        if (! $item) {
            return $this->errorResponse($request, 'Item tidak ditemukan di keranjang.', 404);
        }

        $item->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item dihapus dari keranjang.',
                'cart' => $this->buildCartPayload($userId),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $userId = (int) $request->user()->id;
        $query = CartItem::forUser($userId);

        if ($request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
            $query->where('branch_id', $branchId)->delete();

            return redirect()->route('cart.index')->with('success', 'Keranjang cabang berhasil dikosongkan.');
        }

        $query->delete();

        return redirect()->route('cart.index')->with('success', 'Seluruh keranjang berhasil dikosongkan.');
    }

    /**
     * Build grouped multi-branch cart payload from MySQL cart_items.
     *
     * @return array<string, mixed>
     */
    private function buildCartPayload(int $userId): array
    {
        $records = CartItem::query()
            ->with(['branch', 'product.branchStocks'])
            ->forUser($userId)
            ->orderBy('branch_id')
            ->orderBy('id')
            ->get();

        $groups = [];
        $flatItems = [];
        $subtotal = 0;
        $totalQuantity = 0;
        $hasOutOfStockItems = false;
        $hasStockErrors = false;

        foreach ($records as $record) {
            if (! $record->product || ! $record->branch) {
                continue;
            }

            /** @var BranchStock|null $branchStock */
            $branchStock = $record->product->branchStocks
                ->firstWhere('branch_id', $record->branch_id);

            $stock = $branchStock ? $branchStock->stock : 0;
            $unitPrice = $branchStock && $branchStock->price > 0
                ? $branchStock->price
                : $record->product->price;

            $quantity = $record->quantity;
            $itemSubtotal = $unitPrice * $quantity;

            $isOutOfStock = $stock <= 0;
            $exceedsStock = $quantity > $stock;
            $stockStatus = $isOutOfStock ? 'out_of_stock' : ($stock <= 10 ? 'low_stock' : 'available');

            if ($isOutOfStock) {
                $hasOutOfStockItems = true;
            }
            if ($exceedsStock) {
                $hasStockErrors = true;
            }

            $itemData = [
                'id' => $record->id,
                'product_id' => $record->product_id,
                'product' => [
                    'id' => $record->product->id,
                    'sku' => $record->product->sku,
                    'name' => $record->product->name,
                    'slug' => $record->product->slug,
                    'brand' => $record->product->brand ?? 'Build n Fix',
                    'size' => $record->product->size ?? $record->product->unit,
                    'unit' => $record->product->unit ?? 'Unit',
                    'image' => $record->product->image,
                ],
                'quantity' => $quantity,
                'fulfillment_method' => $record->fulfillment_method ?? 'pickup',
                'unit_price' => $unitPrice,
                'subtotal' => $itemSubtotal,
                'branch_id' => $record->branch_id,
                'branch_name' => $record->branch->name,
                'stock' => $stock,
                'stock_status' => $stockStatus,
                'is_out_of_stock' => $isOutOfStock,
                'exceeds_stock' => $exceedsStock,
            ];

            $bId = $record->branch_id;
            if (! isset($groups[$bId])) {
                $groups[$bId] = [
                    'branch_id' => $record->branch->id,
                    'branch_name' => $record->branch->name,
                    'short_address' => $record->branch->short_address,
                    'city' => $record->branch->city,
                    'items' => [],
                    'item_count' => 0,
                    'total_quantity' => 0,
                    'subtotal' => 0,
                    'valid_item_ids' => [],
                    'has_out_of_stock_items' => false,
                    'has_stock_errors' => false,
                    'is_valid' => true,
                ];
            }

            $groups[$bId]['items'][] = $itemData;
            $groups[$bId]['item_count']++;
            $groups[$bId]['total_quantity'] += $quantity;
            $groups[$bId]['subtotal'] += $itemSubtotal;

            if ($isOutOfStock) {
                $groups[$bId]['has_out_of_stock_items'] = true;
            }
            if ($exceedsStock) {
                $groups[$bId]['has_stock_errors'] = true;
            }
            if (! $isOutOfStock && ! $exceedsStock) {
                $groups[$bId]['valid_item_ids'][] = $record->id;
            }

            $flatItems[] = $itemData;
            $subtotal += $itemSubtotal;
            $totalQuantity += $quantity;
        }

        foreach ($groups as &$group) {
            $group['is_valid'] = $group['item_count'] > 0
                && ! $group['has_out_of_stock_items']
                && ! $group['has_stock_errors'];
        }
        unset($group);

        $itemCount = count($flatItems);

        return [
            'groups' => array_values($groups),
            'items' => $flatItems,
            'branch_count' => count($groups),
            'subtotal' => $subtotal,
            'delivery_fee_label' => 'Dihitung saat checkout',
            'total' => $subtotal,
            'item_count' => $itemCount,
            'total_quantity' => $totalQuantity,
            'has_out_of_stock_items' => $hasOutOfStockItems,
            'has_stock_errors' => $hasStockErrors,
            'is_valid' => $itemCount > 0 && ! $hasOutOfStockItems && ! $hasStockErrors,
        ];
    }

    private function resolveBranch(int|string|null $branchInput): ?Branch
    {
        if (is_numeric($branchInput)) {
            $found = Branch::find((int) $branchInput);
            if ($found) {
                return $found;
            }
        } elseif (is_string($branchInput) && trim($branchInput) !== '') {
            $found = Branch::where('name', trim($branchInput))->first();
            if ($found) {
                return $found;
            }
        }

        return Branch::orderBy('id')->first();
    }

    private function resolveUserCartItem(int $userId, int|string $identifier, ?int $branchId = null): ?CartItem
    {
        $numericId = (int) $identifier;

        $byCartItemId = CartItem::forUser($userId)
            ->where('id', $numericId)
            ->first();

        if ($byCartItemId) {
            return $byCartItemId;
        }

        return CartItem::forUser($userId)
            ->where('product_id', $numericId)
            ->when($branchId !== null, fn ($query) => $query->where('branch_id', $branchId))
            ->first();
    }

    private function errorResponse(Request $request, string $message, int $status): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()->with('error', $message);
    }
}
