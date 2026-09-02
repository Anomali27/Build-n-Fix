<?php

namespace App\Services;

use App\Data\BranchData;
use App\Data\BranchStockData;
use App\Data\ProductData;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * Retrieve the complete cart data with stock & branch validations.
     *
     * @return array<string, mixed>
     */
    public function getCart(): array
    {
        $rawCart = Session::get(self::SESSION_KEY, [
            'items' => [],
            'branch_id' => null,
            'branch_name' => null,
        ]);

        $branchId = $rawCart['branch_id'] ?? null;
        $branchName = $rawCart['branch_name'] ?? null;

        $items = [];
        $subtotal = 0;
        $totalQuantity = 0;
        $hasOutOfStockItems = false;
        $hasStockErrors = false;

        foreach ($rawCart['items'] ?? [] as $productId => $itemData) {
            $product = ProductData::findById((int) $productId);
            if (! $product) {
                continue;
            }

            $itemBranchId = (int) ($itemData['branch_id'] ?? $branchId ?? 1);
            $itemBranch = BranchData::findById($itemBranchId) ?? BranchData::findById(1);
            $itemBranchName = $itemBranch['name'] ?? 'Serdam';

            $stock = BranchStockData::getStock($product['id'], $itemBranchId);
            $stockStatus = BranchStockData::getStockStatus($stock);

            $quantity = (int) ($itemData['quantity'] ?? 1);
            $unitPrice = (int) ($product['price'] ?? 0);
            $itemSubtotal = $unitPrice * $quantity;

            $isOutOfStock = $stock <= 0;
            $exceedsStock = $quantity > $stock;

            if ($isOutOfStock) {
                $hasOutOfStockItems = true;
            }
            if ($exceedsStock) {
                $hasStockErrors = true;
            }

            $subtotal += $itemSubtotal;
            $totalQuantity += $quantity;

            $items[] = [
                'product_id' => $product['id'],
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $itemSubtotal,
                'branch_id' => $itemBranchId,
                'branch_name' => $itemBranchName,
                'stock' => $stock,
                'stock_status' => $stockStatus,
                'is_out_of_stock' => $isOutOfStock,
                'exceeds_stock' => $exceedsStock,
            ];
        }

        $itemCount = count($items);
        if ($itemCount === 0) {
            $branchId = null;
            $branchName = null;
        } elseif ($branchId === null && ! empty($items)) {
            $branchId = $items[0]['branch_id'];
            $branchName = $items[0]['branch_name'];
        }

        $isValid = $itemCount > 0 && ! $hasOutOfStockItems && ! $hasStockErrors;

        return [
            'items' => $items,
            'branch_id' => $branchId,
            'branch_name' => $branchName,
            'subtotal' => $subtotal,
            'delivery_fee_label' => 'Calculated at checkout',
            'total' => $subtotal,
            'item_count' => $itemCount,
            'total_quantity' => $totalQuantity,
            'has_out_of_stock_items' => $hasOutOfStockItems,
            'has_stock_errors' => $hasStockErrors,
            'is_valid' => $isValid,
        ];
    }

    /**
     * Add a product to the cart with strict branch & stock validation.
     *
     * @param  int|string  $branch  (branch_id int or branch_name string)
     * @return array{success: bool, message: string, code?: string, existing_branch_name?: string, new_branch_name?: string, cart?: array<string, mixed>}
     */
    public function addProduct(int $productId, int $quantity = 1, int|string $branch = 1, bool $force = false): array
    {
        $product = ProductData::findById($productId);
        if (! $product) {
            return [
                'success' => false,
                'message' => 'Product not found.',
            ];
        }

        // Resolve branch ID & Name
        $targetBranch = is_numeric($branch)
            ? BranchData::findById((int) $branch)
            : BranchData::findByName((string) $branch);

        if (! $targetBranch) {
            $targetBranch = BranchData::findById(1); // Default Serdam
        }

        $targetBranchId = (int) $targetBranch['id'];
        $targetBranchName = (string) $targetBranch['name'];

        $cart = Session::get(self::SESSION_KEY, [
            'items' => [],
            'branch_id' => null,
            'branch_name' => null,
        ]);

        $existingItems = $cart['items'] ?? [];
        $currentCartBranchId = $cart['branch_id'] ?? null;

        // ONE BRANCH PER TRANSACTION BUSINESS RULE
        if (! empty($existingItems) && $currentCartBranchId !== null && (int) $currentCartBranchId !== $targetBranchId) {
            if (! $force) {
                $existingBranchObj = BranchData::findById((int) $currentCartBranchId);
                $existingBranchName = $existingBranchObj['name'] ?? 'Branch '.$currentCartBranchId;

                return [
                    'success' => false,
                    'code' => 'BRANCH_MISMATCH',
                    'message' => 'Your cart contains products from another branch.',
                    'existing_branch_name' => $existingBranchName,
                    'new_branch_name' => $targetBranchName,
                ];
            }

            // Force add: clear existing cart for new branch
            $cart = [
                'items' => [],
                'branch_id' => $targetBranchId,
                'branch_name' => $targetBranchName,
            ];
            $existingItems = [];
        }

        // Validate stock
        $currentQtyInCart = (int) ($existingItems[$productId]['quantity'] ?? 0);
        $newQty = $currentQtyInCart + $quantity;

        $availableStock = BranchStockData::getStock($productId, $targetBranchId);

        if ($newQty > $availableStock) {
            return [
                'success' => false,
                'message' => 'Quantity exceeds available stock.',
            ];
        }

        // Update cart items
        $existingItems[$productId] = [
            'product_id' => $productId,
            'quantity' => $newQty,
            'branch_id' => $targetBranchId,
            'branch_name' => $targetBranchName,
        ];

        $cart['items'] = $existingItems;
        $cart['branch_id'] = $targetBranchId;
        $cart['branch_name'] = $targetBranchName;

        Session::put(self::SESSION_KEY, $cart);

        return [
            'success' => true,
            'message' => 'Product successfully added to cart.',
            'cart' => $this->getCart(),
        ];
    }

    /**
     * Update quantity for a cart item.
     *
     * @return array{success: bool, message: string, cart?: array<string, mixed>}
     */
    public function updateQuantity(int $productId, int $quantity): array
    {
        $cart = Session::get(self::SESSION_KEY, [
            'items' => [],
            'branch_id' => null,
            'branch_name' => null,
        ]);

        if (! isset($cart['items'][$productId])) {
            return [
                'success' => false,
                'message' => 'Item not found in cart.',
            ];
        }

        if ($quantity <= 0) {
            return $this->removeProduct($productId);
        }

        $branchId = (int) ($cart['items'][$productId]['branch_id'] ?? $cart['branch_id'] ?? 1);
        $availableStock = BranchStockData::getStock($productId, $branchId);

        if ($quantity > $availableStock) {
            return [
                'success' => false,
                'message' => 'Quantity exceeds available stock.',
            ];
        }

        $cart['items'][$productId]['quantity'] = $quantity;
        Session::put(self::SESSION_KEY, $cart);

        return [
            'success' => true,
            'message' => 'Cart item quantity updated.',
            'cart' => $this->getCart(),
        ];
    }

    /**
     * Remove a product from the cart.
     *
     * @return array{success: bool, message: string, cart?: array<string, mixed>}
     */
    public function removeProduct(int $productId): array
    {
        $cart = Session::get(self::SESSION_KEY, [
            'items' => [],
            'branch_id' => null,
            'branch_name' => null,
        ]);

        if (isset($cart['items'][$productId])) {
            unset($cart['items'][$productId]);
        }

        if (empty($cart['items'])) {
            $cart['branch_id'] = null;
            $cart['branch_name'] = null;
        }

        Session::put(self::SESSION_KEY, $cart);

        return [
            'success' => true,
            'message' => 'Item removed from cart.',
            'cart' => $this->getCart(),
        ];
    }

    /**
     * Clear all items in the cart.
     */
    public function clearCart(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
