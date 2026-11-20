<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\ProcessPaymentRequest;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    private const SESSION_KEY = 'checkout';

    private const SELECTED_ITEMS_KEY = 'checkout.selected_item_ids';

    private const DELIVERY_FEE_PER_BRANCH = 15000;

    public function index(Request $request): View|RedirectResponse
    {
        if ($denied = $this->authorizeCustomer($request)) {
            return $denied;
        }

        /** @var User $user */
        $user = $request->user();
        $requestedItems = array_values(array_filter(array_map('intval', (array) $request->query('items', []))));
        $requestedBranch = $request->query('branch');

        if (count($requestedItems) > 0) {
            $matchedIds = CartItem::forUser((int) $user->id)
                ->whereIn('id', $requestedItems)
                ->pluck('id')
                ->all();
            Session::put(self::SELECTED_ITEMS_KEY, $matchedIds);
        } elseif ($requestedBranch !== null && trim((string) $requestedBranch) !== '') {
            $branchId = $this->resolveBranchId($requestedBranch);
            $matchedIds = $branchId !== null
                ? CartItem::forUser((int) $user->id)->where('branch_id', $branchId)->pluck('id')->all()
                : [];
            Session::put(self::SELECTED_ITEMS_KEY, $matchedIds);
        } elseif (! $request->boolean('keep')) {
            Session::forget(self::SELECTED_ITEMS_KEY);
        }

        $checkoutData = $this->buildCheckoutData($user);

        if (($checkoutData['cart']['item_count'] ?? 0) === 0) {
            Session::forget(self::SELECTED_ITEMS_KEY);

            return redirect()->route('cart.index')->with('info', 'Keranjang Anda kosong atau tidak ada item yang dipilih.');
        }

        return view('checkout.index', compact('checkoutData'));
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        if ($denied = $this->authorizeCustomer($request)) {
            return $denied;
        }

        /** @var User $user */
        $user = $request->user();
        $checkoutData = $this->buildCheckoutData($user);

        if (($checkoutData['cart']['item_count'] ?? 0) === 0 || ! ($checkoutData['is_valid'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('error', $checkoutData['validation_errors'][0] ?? 'Validasi checkout gagal. Periksa kembali stok item Anda.');
        }

        $validated = $request->validated();
        $itemFulfillments = (array) ($validated['item_fulfillments'] ?? []);
        $fallbackMethod = isset($validated['fulfillment_method']) && in_array($validated['fulfillment_method'], ['pickup', 'delivery'], true)
            ? $validated['fulfillment_method']
            : null;

        $hasSelectedFilter = Session::has(self::SELECTED_ITEMS_KEY);
        $selectedIds = array_map('intval', (array) Session::get(self::SELECTED_ITEMS_KEY, []));

        $cartItems = CartItem::query()
            ->forUser((int) $user->id)
            ->when($hasSelectedFilter, fn ($q) => $q->whereIn('id', $selectedIds))
            ->get();

        $resolvedMethods = [];
        foreach ($cartItems as $cartItem) {
            $chosenMethod = $itemFulfillments[$cartItem->id]
                ?? $itemFulfillments[(string) $cartItem->id]
                ?? $fallbackMethod
                ?? ($cartItem->fulfillment_method ?: 'pickup');

            $chosenMethod = strtolower((string) $chosenMethod) === 'delivery' ? 'delivery' : 'pickup';

            if ($cartItem->fulfillment_method !== $chosenMethod) {
                $cartItem->update(['fulfillment_method' => $chosenMethod]);
            }

            $resolvedMethods[$cartItem->id] = $chosenMethod;
        }

        $hasDelivery = in_array('delivery', $resolvedMethods, true);
        $uniqueMethods = array_values(array_unique(array_values($resolvedMethods)));
        $fulfillmentMethod = count($uniqueMethods) === 1
            ? $uniqueMethods[0]
            : ($hasDelivery ? 'mixed' : 'pickup');

        $deliveryAddress = $hasDelivery ? [
            'recipient_name' => $validated['recipient_name'] ?? $validated['customer_name'],
            'phone' => $validated['phone'] ?? $validated['customer_phone'],
            'address' => $validated['address'] ?? '',
            'city' => $validated['city'] ?? 'Pontianak',
            'postal_code' => $validated['postal_code'] ?? '',
            'notes' => $validated['notes'] ?? '',
        ] : null;

        Session::put(self::SESSION_KEY, [
            'fulfillment_method' => $fulfillmentMethod,
            'item_fulfillments' => $resolvedMethods,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'delivery_address' => $deliveryAddress,
        ]);

        return redirect()->route('checkout.payment');
    }

    public function payment(Request $request): View|RedirectResponse
    {
        if ($denied = $this->authorizeCustomer($request)) {
            return $denied;
        }

        /** @var User $user */
        $user = $request->user();
        $checkoutData = $this->buildCheckoutData($user);

        if (($checkoutData['cart']['item_count'] ?? 0) === 0) {
            return redirect()->route('cart.index')->with('info', 'Keranjang Anda kosong.');
        }

        if (! ($checkoutData['is_valid'] ?? false)) {
            return redirect()->route('checkout.index', ['keep' => 1])
                ->with('error', $checkoutData['validation_errors'][0] ?? 'Stok item tidak mencukupi.');
        }

        return view('checkout.payment', compact('checkoutData'));
    }

    public function processPayment(ProcessPaymentRequest $request): RedirectResponse
    {
        if ($denied = $this->authorizeCustomer($request)) {
            return $denied;
        }

        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();
        $checkoutData = $this->buildCheckoutData($user);

        if (($checkoutData['cart']['item_count'] ?? 0) === 0 || ! ($checkoutData['is_valid'] ?? false)) {
            return redirect()->route('cart.index')
                ->with('error', 'Sesi checkout tidak valid atau keranjang kosong.');
        }

        if (strtolower((string) ($validated['simulation_outcome'] ?? 'success')) === 'failed') {
            return redirect()->route('checkout.payment')
                ->withInput()
                ->with('payment_failed', true)
                ->with('error', 'Pembayaran tidak dapat diselesaikan. Silakan coba lagi.');
        }

        $paymentMethod = $validated['payment_method'];
        $fulfillmentMethod = $checkoutData['fulfillment_method'];
        $customer = $checkoutData['customer'];
        $deliveryAddress = $checkoutData['delivery_address'] ?? [];
        $hasSelectedFilter = Session::has(self::SELECTED_ITEMS_KEY);
        $selectedIds = array_map('intval', (array) Session::get(self::SELECTED_ITEMS_KEY, []));

        try {
            /** @var array<int, Order> $createdOrders */
            $createdOrders = DB::transaction(function () use (
                $user,
                $paymentMethod,
                $fulfillmentMethod,
                $customer,
                $deliveryAddress,
                $hasSelectedFilter,
                $selectedIds
            ): array {
                /** @var Collection<int, CartItem> $cartItems */
                $cartItems = CartItem::query()
                    ->with(['branch', 'product'])
                    ->forUser((int) $user->id)
                    ->when($hasSelectedFilter, fn ($q) => $q->whereIn('id', $selectedIds))
                    ->orderBy('branch_id')
                    ->orderBy('id')
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw new RuntimeException('Tidak ada item keranjang yang siap dibayar.');
                }

                $branchIds = $cartItems->pluck('branch_id')->unique()->all();
                $productIds = $cartItems->pluck('product_id')->unique()->all();

                $lockedStocks = BranchStock::query()
                    ->whereIn('branch_id', $branchIds)
                    ->whereIn('product_id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy(fn (BranchStock $bs): string => $bs->branch_id.'_'.$bs->product_id);

                foreach ($cartItems as $item) {
                    if (! $item->product || ! $item->branch) {
                        throw new RuntimeException('Produk atau cabang pada keranjang sudah tidak tersedia.');
                    }

                    $stockKey = $item->branch_id.'_'.$item->product_id;
                    /** @var BranchStock|null $branchStock */
                    $branchStock = $lockedStocks->get($stockKey);

                    if (! $branchStock || $branchStock->stock < $item->quantity) {
                        throw new RuntimeException('Stok '.$item->product->name.' di Cabang '.$item->branch->name.' tidak mencukupi.');
                    }
                }

                $orders = [];
                $groupedCartItems = $cartItems->groupBy(
                    fn (CartItem $item): string => $item->branch_id.'_'.(strtolower((string) ($item->fulfillment_method ?: 'pickup')) === 'delivery' ? 'delivery' : 'pickup')
                );

                foreach ($groupedCartItems as $branchItems) {
                    /** @var CartItem $firstItem */
                    $firstItem = $branchItems->first();
                    /** @var Branch $branch */
                    $branch = $firstItem->branch;
                    $branchId = $branch->id;
                    $itemFulfillment = strtolower((string) ($firstItem->fulfillment_method ?: 'pickup')) === 'delivery'
                        ? 'delivery'
                        : 'pickup';
                    $isDelivery = $itemFulfillment === 'delivery';
                    $deliveryFeeForOrder = $isDelivery ? self::DELIVERY_FEE_PER_BRANCH : 0;
                    $orderStatus = $isDelivery ? 'proses' : 'ready_to_pick_up';

                    $branchSubtotal = 0;
                    $linePayloads = [];

                    foreach ($branchItems as $item) {
                        $stockKey = $branchId.'_'.$item->product_id;
                        /** @var BranchStock $branchStock */
                        $branchStock = $lockedStocks->get($stockKey);
                        $unitPrice = $branchStock->price > 0 ? $branchStock->price : $item->product->price;
                        $lineSubtotal = $unitPrice * $item->quantity;
                        $branchSubtotal += $lineSubtotal;

                        $linePayloads[] = [
                            'item' => $item,
                            'branch_stock' => $branchStock,
                            'unit_price' => $unitPrice,
                            'subtotal' => $lineSubtotal,
                        ];
                    }

                    $branchTotal = $branchSubtotal + $deliveryFeeForOrder;

                    $order = Order::create([
                        'order_number' => 'TMP-'.Str::uuid()->toString(),
                        'user_id' => $user->id,
                        'branch_id' => $branch->id,
                        'fulfillment_method' => $itemFulfillment,
                        'order_status' => $orderStatus,
                        'payment_status' => 'paid',
                        'customer_name' => $customer['name'],
                        'customer_email' => $customer['email'],
                        'customer_phone' => $customer['phone'],
                        'recipient_name' => $isDelivery ? ($deliveryAddress['recipient_name'] ?? $customer['name']) : null,
                        'recipient_phone' => $isDelivery ? ($deliveryAddress['phone'] ?? $customer['phone']) : null,
                        'delivery_address' => $isDelivery ? ($deliveryAddress['address'] ?? null) : null,
                        'delivery_city' => $isDelivery ? ($deliveryAddress['city'] ?? null) : null,
                        'delivery_postal_code' => $isDelivery ? ($deliveryAddress['postal_code'] ?? null) : null,
                        'delivery_notes' => $isDelivery ? ($deliveryAddress['notes'] ?? null) : null,
                        'subtotal' => $branchSubtotal,
                        'delivery_fee' => $deliveryFeeForOrder,
                        'total' => $branchTotal,
                    ]);

                    $orderNumber = 'BNF-'.now()->format('Ymd').'-'.str_pad((string) $order->id, 3, '0', STR_PAD_LEFT);
                    $order->update(['order_number' => $orderNumber]);

                    foreach ($linePayloads as $payload) {
                        /** @var CartItem $cartItem */
                        $cartItem = $payload['item'];
                        /** @var BranchStock $branchStock */
                        $branchStock = $payload['branch_stock'];
                        $product = $cartItem->product;

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_sku' => $product->sku,
                            'product_name' => $product->name,
                            'product_brand' => $product->brand ?? 'Build n Fix',
                            'product_unit' => $product->unit ?? $product->size ?? 'Unit',
                            'product_image' => $product->image,
                            'quantity' => $cartItem->quantity,
                            'unit_price' => $payload['unit_price'],
                            'subtotal' => $payload['subtotal'],
                        ]);

                        $prevStock = $branchStock->stock;
                        $nextStock = $prevStock - $cartItem->quantity;
                        $branchStock->update(['stock' => $nextStock]);

                        $movement = StockMovement::create([
                            'reference_no' => 'TMP-'.Str::uuid()->toString(),
                            'moved_at' => now(),
                            'product_id' => $product->id,
                            'branch_id' => $branch->id,
                            'user_id' => $user->id,
                            'actor_name' => $customer['name'],
                            'type' => 'out',
                            'quantity' => $cartItem->quantity,
                            'previous_stock' => $prevStock,
                            'current_stock' => $nextStock,
                            'source' => 'Penjualan / Order #'.$orderNumber,
                            'notes' => 'Checkout '.$itemFulfillment.' ('.$paymentMethod.')',
                        ]);

                        $movement->update([
                            'reference_no' => 'MOV-'.now()->format('Y').'-'.str_pad((string) $movement->id, 3, '0', STR_PAD_LEFT),
                        ]);
                    }

                    $payment = Payment::create([
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'branch_id' => $branch->id,
                        'payment_code' => 'TMP-'.Str::uuid()->toString(),
                        'payment_method' => $paymentMethod,
                        'amount' => $branchTotal,
                        'status' => 'paid',
                        'paid_at' => now(),
                        'proof_url' => null,
                    ]);

                    $payment->update([
                        'payment_code' => 'PAY-'.now()->format('Ymd').'-'.str_pad((string) $payment->id, 3, '0', STR_PAD_LEFT),
                    ]);

                    $orders[] = $order;
                }

                CartItem::forUser((int) $user->id)
                    ->whereIn('id', $cartItems->pluck('id')->all())
                    ->delete();

                return $orders;
            });
        } catch (RuntimeException $e) {
            return redirect()->route('checkout.index', ['keep' => 1])
                ->with('error', $e->getMessage());
        }

        Session::forget(self::SESSION_KEY);
        Session::forget(self::SELECTED_ITEMS_KEY);

        $latestOrder = $createdOrders[0]->fresh(['branch', 'items', 'latestPayment']);
        $orderNumbers = implode(', ', array_map(fn (Order $o): string => $o->order_number, $createdOrders));
        $message = count($createdOrders) > 1
            ? 'Pembayaran berhasil! '.count($createdOrders).' pesanan multi-cabang dibuat: '.$orderNumbers.'.'
            : 'Payment Successful! Pesanan '.$orderNumbers.' berhasil dibuat.';

        return redirect()->route('orders.index', ['tab' => 'success'])
            ->with('payment_success', true)
            ->with('latest_order', $latestOrder?->toDisplayArray())
            ->with('success', $message);
    }

    /**
     * Build complete checkout payload directly from user's MySQL cart_items and session state.
     *
     * @return array<string, mixed>
     */
    private function buildCheckoutData(User $user): array
    {
        $hasSelectedFilter = Session::has(self::SELECTED_ITEMS_KEY);
        $selectedIds = array_map('intval', (array) Session::get(self::SELECTED_ITEMS_KEY, []));

        $records = CartItem::query()
            ->with(['branch', 'product.branchStocks'])
            ->forUser((int) $user->id)
            ->when($hasSelectedFilter, fn ($q) => $q->whereIn('id', $selectedIds))
            ->orderBy('branch_id')
            ->orderBy('id')
            ->get();

        $branchesMap = [];
        $items = [];
        $subtotal = 0;
        $totalQuantity = 0;
        $hasOutOfStockItems = false;
        $hasStockErrors = false;
        $fulfillmentChoices = [];
        $deliveryBranchIds = [];
        $pickupBranchIds = [];

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

            $itemSubtotal = $unitPrice * $record->quantity;
            $isOutOfStock = $stock <= 0;
            $exceedsStock = $record->quantity > $stock;

            if ($isOutOfStock) {
                $hasOutOfStockItems = true;
            }
            if ($exceedsStock) {
                $hasStockErrors = true;
            }

            $itemFulfillment = strtolower((string) ($record->fulfillment_method ?? 'pickup')) === 'delivery'
                ? 'delivery'
                : 'pickup';
            $fulfillmentChoices[] = $itemFulfillment;

            if ($itemFulfillment === 'delivery') {
                $deliveryBranchIds[$record->branch_id] = true;
            } else {
                $pickupBranchIds[$record->branch_id] = true;
            }

            $rawImg = (string) ($record->product->image ?? '');
            $resolvedImg = $rawImg === ''
                ? 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600&auto=format&fit=crop&q=80'
                : (Str::startsWith($rawImg, ['http://', 'https://', '/']) ? $rawImg : '/'.$rawImg);

            $itemPayload = [
                'id' => $record->id,
                'product_id' => $record->product_id,
                'branch_id' => $record->branch_id,
                'branch_name' => $record->branch->name,
                'fulfillment_method' => $itemFulfillment,
                'product' => [
                    'id' => $record->product->id,
                    'sku' => $record->product->sku,
                    'name' => $record->product->name,
                    'brand' => $record->product->brand ?? 'Build n Fix',
                    'unit' => $record->product->unit ?? $record->product->size ?? 'Unit',
                    'image' => $resolvedImg,
                ],
                'quantity' => $record->quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $itemSubtotal,
                'stock' => $stock,
                'is_out_of_stock' => $isOutOfStock,
                'exceeds_stock' => $exceedsStock,
            ];

            $items[] = $itemPayload;
            $subtotal += $itemSubtotal;
            $totalQuantity += $record->quantity;

            $bId = $record->branch_id;
            if (! isset($branchesMap[$bId])) {
                $branchesMap[$bId] = [
                    'id' => $record->branch->id,
                    'name' => $record->branch->name,
                    'short_address' => $record->branch->short_address ?? '',
                    'address' => $record->branch->address ?? '',
                    'city' => $record->branch->city ?? 'Pontianak',
                    'subtotal' => 0,
                    'items' => [],
                ];
            }

            $branchesMap[$bId]['subtotal'] += $itemSubtotal;
            $branchesMap[$bId]['items'][] = $itemPayload;
        }

        $branches = array_values($branchesMap);
        $branchCount = count($branches);
        $primaryBranch = $branches[0] ?? [
            'id' => 1,
            'name' => 'Serdam',
            'short_address' => '',
            'address' => '',
            'city' => 'Pontianak',
        ];

        $savedCheckout = Session::get(self::SESSION_KEY, []);
        $uniqueMethods = array_values(array_unique($fulfillmentChoices));
        $fulfillmentMethod = count($uniqueMethods) === 1
            ? $uniqueMethods[0]
            : (count($uniqueMethods) > 1 ? 'mixed' : 'pickup');

        $deliveryBranchCount = count($deliveryBranchIds);
        $pickupBranchCount = count($pickupBranchIds);

        $customer = [
            'name' => $savedCheckout['customer_name'] ?? $user->name,
            'email' => $savedCheckout['customer_email'] ?? $user->email,
            'phone' => $savedCheckout['customer_phone'] ?? $user->phone ?? '081234567890',
        ];

        $deliveryAddress = $savedCheckout['delivery_address'] ?? [
            'recipient_name' => $customer['name'],
            'phone' => $customer['phone'],
            'address' => '',
            'city' => 'Pontianak',
            'postal_code' => '',
            'notes' => '',
        ];

        $deliveryFeePerBranch = self::DELIVERY_FEE_PER_BRANCH;
        $deliveryFee = $deliveryBranchCount * $deliveryFeePerBranch;
        $total = $subtotal + $deliveryFee;

        $errors = [];
        if (count($items) === 0) {
            $errors[] = 'Keranjang belanja kosong. Silakan pilih produk terlebih dahulu.';
        }
        if ($hasOutOfStockItems) {
            $errors[] = 'Beberapa produk di keranjang sedang habis stoknya.';
        }
        if ($hasStockErrors) {
            $errors[] = 'Jumlah beberapa item melebihi stok cabang yang tersedia.';
        }

        return [
            'cart' => [
                'items' => $items,
                'item_count' => count($items),
                'total_quantity' => $totalQuantity,
                'subtotal' => $subtotal,
            ],
            'customer' => $customer,
            'branch' => $primaryBranch,
            'branches' => $branches,
            'branch_count' => $branchCount,
            'fulfillment_method' => $fulfillmentMethod,
            'has_delivery' => $deliveryBranchCount > 0,
            'has_pickup' => $pickupBranchCount > 0,
            'delivery_branch_count' => $deliveryBranchCount,
            'pickup_branch_count' => $pickupBranchCount,
            'delivery_address' => $deliveryAddress,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'delivery_fee_per_branch' => $deliveryFeePerBranch,
            'total' => $total,
            'is_valid' => count($errors) === 0,
            'validation_errors' => $errors,
        ];
    }

    private function resolveBranchId(mixed $branchInput): ?int
    {
        if (is_numeric($branchInput)) {
            $id = Branch::where('id', (int) $branchInput)->value('id');

            return $id !== null ? (int) $id : null;
        }

        $clean = trim((string) $branchInput);
        if ($clean === '') {
            return null;
        }

        $id = Branch::where('name', $clean)->value('id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * Authorize that the current authenticated user has customer role.
     */
    private function authorizeCustomer(Request $request): ?RedirectResponse
    {
        $role = $request->user()?->role;

        if ($role !== 'customer') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak. Fitur Checkout hanya dapat diakses oleh akun Customer.');
        }

        return null;
    }
}
