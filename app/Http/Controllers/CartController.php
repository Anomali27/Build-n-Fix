<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the Cart page.
     */
    public function index()
    {
        $cart = $this->cartService->getCart();

        return view('cart.index', [
            'cart' => $cart,
        ]);
    }

    /**
     * Add product to cart.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1',
            'branch' => 'nullable',
            'force' => 'nullable|boolean',
        ]);

        $productId = (int) $validated['product_id'];
        $quantity = (int) ($validated['quantity'] ?? 1);
        $branch = $validated['branch'] ?? 1;
        $force = (bool) ($validated['force'] ?? false);

        $result = $this->cartService->addProduct($productId, $quantity, $branch, $force);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if (! $result['success']) {
            if (($result['code'] ?? null) === 'BRANCH_MISMATCH') {
                return redirect()->back()->with('branch_mismatch', [
                    'message' => $result['message'],
                    'existing_branch' => $result['existing_branch_name'],
                    'new_branch' => $result['new_branch_name'],
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'branch' => $branch,
                ]);
            }

            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    /**
     * Update quantity for a cart product.
     */
    public function update(Request $request, int|string $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $productId = (int) $product;
        $quantity = (int) $validated['quantity'];

        $result = $this->cartService->updateQuantity($productId, $quantity);

        if (! $result['success']) {
            return redirect()->route('cart.index')->with('error', $result['message']);
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    /**
     * Remove a single product from cart.
     */
    public function destroy(int|string $product)
    {
        $productId = (int) $product;
        $result = $this->cartService->removeProduct($productId);

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    /**
     * Clear all items in cart.
     */
    public function clear()
    {
        $this->cartService->clearCart();

        return redirect()->route('cart.index')->with('success', 'Cart successfully cleared.');
    }
}
