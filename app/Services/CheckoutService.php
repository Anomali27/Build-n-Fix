<?php

namespace App\Services;

use App\Repositories\BranchRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Session;

class CheckoutService
{
    private const SESSION_KEY = 'checkout';

    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Get complete checkout state from CartService, User session, and Checkout session.
     *
     * @return array<string, mixed>
     */
    public function getCheckoutData(): array
    {
        $cart = $this->cartService->getCart();
        $savedCheckout = Session::get(self::SESSION_KEY, []);

        // 1. Customer Information
        $userSession = Session::get('user', []);
        $customer = [
            'name' => $savedCheckout['customer_name'] ?? $userSession['name'] ?? 'Customer',
            'email' => $savedCheckout['customer_email'] ?? $userSession['email'] ?? 'customer@buildnfix.test',
            'phone' => $savedCheckout['customer_phone'] ?? $userSession['phone'] ?? '081234567890',
        ];

        // 2. Branch Confirmation (One Branch Per Transaction)
        $branchId = (int) ($cart['branch_id'] ?? 1);
        $branchObj = BranchRepository::findById($branchId) ?? BranchRepository::findById(1);
        $branch = [
            'id' => $branchObj['id'] ?? 1,
            'name' => $branchObj['name'] ?? 'Serdam',
            'city' => $branchObj['city'] ?? 'Pontianak',
            'status' => $branchObj['status'] ?? 'Buka',
            'image' => $branchObj['image'] ?? '',
        ];

        // 3. Fulfillment Method (pickup / delivery)
        $fulfillmentMethod = strtolower($savedCheckout['fulfillment_method'] ?? 'pickup');

        // 4. Delivery Address if delivery
        $deliveryAddress = $savedCheckout['delivery_address'] ?? [
            'recipient_name' => $customer['name'],
            'phone' => $customer['phone'],
            'address' => '',
            'city' => 'Pontianak',
            'postal_code' => '',
            'notes' => '',
        ];

        // 5. Calculate Subtotal, Delivery Fee & Total
        $subtotal = $this->calculateSubtotal($cart);
        $deliveryFee = $this->calculateDeliveryFee($fulfillmentMethod, $deliveryAddress);
        $total = $this->calculateTotal($subtotal, $deliveryFee);

        // 6. Validations
        $validation = $this->validateCart($cart);

        return [
            'cart' => $cart,
            'customer' => $customer,
            'branch' => $branch,
            'fulfillment_method' => $fulfillmentMethod,
            'delivery_address' => $deliveryAddress,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'is_valid' => $validation['is_valid'],
            'validation_errors' => $validation['errors'],
        ];
    }

    /**
     * Validate cart items, branch consistency, and stock.
     *
     * @param  array<string, mixed>  $cart
     * @return array{is_valid: bool, errors: array<int, string>}
     */
    public function validateCart(array $cart): array
    {
        $errors = [];

        if (empty($cart['items']) || ($cart['item_count'] ?? 0) === 0) {
            $errors[] = 'Shopping cart is empty. Please add items before checking out.';
        }

        if ($cart['has_out_of_stock_items'] ?? false) {
            $errors[] = 'Your cart contains items that are currently out of stock.';
        }

        if ($cart['has_stock_errors'] ?? false) {
            $errors[] = 'Some items in your cart exceed available branch stock.';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Calculate subtotal of items in cart.
     *
     * @param  array<string, mixed>  $cart
     */
    public function calculateSubtotal(array $cart): int
    {
        return (int) ($cart['subtotal'] ?? 0);
    }

    /**
     * Calculate delivery fee based on fulfillment method and location rules.
     *
     * @param  array<string, mixed>|string|null  $address
     */
    public function calculateDeliveryFee(string $fulfillmentMethod, array|string|null $address = null): int
    {
        if (strtolower($fulfillmentMethod) === 'pickup') {
            return 0;
        }

        // Build n Fix standard flat delivery fee for Pontianak area
        return 15000;
    }

    /**
     * Calculate final total.
     */
    public function calculateTotal(int $subtotal, int $deliveryFee): int
    {
        return $subtotal + $deliveryFee;
    }

    /**
     * Validate fulfillment method input.
     */
    public function validateFulfillment(string $method): bool
    {
        return in_array(strtolower($method), ['pickup', 'delivery'], true);
    }

    /**
     * Validate delivery address fields.
     *
     * @param  array<string, mixed>  $address
     * @return array{is_valid: bool, errors: array<string, string>}
     */
    public function validateAddress(array $address): array
    {
        $errors = [];

        if (empty(trim($address['recipient_name'] ?? ''))) {
            $errors['recipient_name'] = 'Recipient name is required.';
        }

        if (empty(trim($address['phone'] ?? ''))) {
            $errors['phone'] = 'Phone number is required.';
        }

        if (empty(trim($address['address'] ?? ''))) {
            $errors['address'] = 'Street address is required.';
        }

        if (empty(trim($address['city'] ?? ''))) {
            $errors['city'] = 'City is required.';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Save form inputs to session and prepare checkout state.
     *
     * @param  array<string, mixed>  $input
     * @return array{success: bool, errors?: array<string, mixed>|array<int, string>, checkoutData?: array<string, mixed>}
     */
    public function preparePayment(array $input): array
    {
        $cart = $this->cartService->getCart();
        $cartValidation = $this->validateCart($cart);

        if (! $cartValidation['is_valid']) {
            return [
                'success' => false,
                'errors' => $cartValidation['errors'],
            ];
        }

        $fulfillmentMethod = strtolower($input['fulfillment_method'] ?? 'pickup');

        if (! $this->validateFulfillment($fulfillmentMethod)) {
            return [
                'success' => false,
                'errors' => ['fulfillment_method' => 'Invalid fulfillment method chosen.'],
            ];
        }

        $deliveryAddress = null;
        if ($fulfillmentMethod === 'delivery') {
            $deliveryAddress = [
                'recipient_name' => $input['recipient_name'] ?? '',
                'phone' => $input['phone'] ?? '',
                'address' => $input['address'] ?? '',
                'city' => $input['city'] ?? 'Pontianak',
                'postal_code' => $input['postal_code'] ?? '',
                'notes' => $input['notes'] ?? '',
            ];

            $addressValidation = $this->validateAddress($deliveryAddress);
            if (! $addressValidation['is_valid']) {
                return [
                    'success' => false,
                    'errors' => $addressValidation['errors'],
                ];
            }
        }

        $customerName = $input['customer_name'] ?? $input['recipient_name'] ?? 'Customer';
        $customerEmail = $input['customer_email'] ?? 'customer@buildnfix.test';
        $customerPhone = $input['customer_phone'] ?? $input['phone'] ?? '081234567890';

        $subtotal = $this->calculateSubtotal($cart);
        $deliveryFee = $this->calculateDeliveryFee($fulfillmentMethod, $deliveryAddress);
        $total = $this->calculateTotal($subtotal, $deliveryFee);

        $checkoutState = [
            'branch_id' => $cart['branch_id'] ?? 1,
            'branch_name' => $cart['branch_name'] ?? 'Serdam',
            'fulfillment_method' => $fulfillmentMethod,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'delivery_address' => $deliveryAddress,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
        ];

        Session::put(self::SESSION_KEY, $checkoutState);

        return [
            'success' => true,
            'checkoutData' => $this->getCheckoutData(),
        ];
    }

    /**
     * Process order creation after payment simulation.
     *
     * @return array{success: bool, order?: array<string, mixed>, message?: string}
     */
    public function processPayment(): array
    {
        $checkoutData = $this->getCheckoutData();
        $cart = $checkoutData['cart'];

        if (! $checkoutData['is_valid']) {
            return [
                'success' => false,
                'message' => 'Checkout state is invalid.',
            ];
        }

        $items = [];
        foreach ($cart['items'] as $item) {
            $items[] = [
                'product_id' => $item['product_id'],
                'sku' => $item['product']['sku'] ?? 'SKU-'.$item['product_id'],
                'product_name' => $item['product']['name'] ?? 'Product Item',
                'brand' => $item['product']['brand'] ?? 'Build n Fix',
                'image' => $item['product']['image'] ?? '',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
            ];
        }

        $userSession = Session::get('user', []);
        $userId = (int) ($userSession['user_id'] ?? 1);

        $formattedAddress = null;
        if ($checkoutData['fulfillment_method'] === 'delivery' && ! empty($checkoutData['delivery_address'])) {
            $addr = $checkoutData['delivery_address'];
            $formattedAddress = implode(', ', array_filter([
                $addr['address'] ?? '',
                $addr['city'] ?? '',
                $addr['postal_code'] ?? '',
            ]));
        }

        $branchObj = BranchRepository::findById((int) $checkoutData['branch']['id']) ?? BranchRepository::findById(1);

        $orderData = [
            'user_id' => $userId,
            'branch_id' => $checkoutData['branch']['id'],
            'branch_name' => $checkoutData['branch']['name'],
            'branch_address' => 'Jl. '.$checkoutData['branch']['name'].' No. 88, Pontianak',
            'fulfillment_method' => $checkoutData['fulfillment_method'],
            'payment_status' => 'paid',
            'payment_method' => 'Build n Fix Payment Simulation',
            'order_status' => $checkoutData['fulfillment_method'] === 'pickup' ? 'ready_to_pick_up' : 'proses',
            'customer_name' => $checkoutData['customer']['name'],
            'customer_email' => $checkoutData['customer']['email'],
            'customer_phone' => $checkoutData['customer']['phone'],
            'delivery_address' => $formattedAddress,
            'subtotal' => $checkoutData['subtotal'],
            'delivery_fee' => $checkoutData['delivery_fee'],
            'total' => $checkoutData['total'],
            'items' => $items,
        ];

        $order = OrderRepository::create($orderData);

        // Clear cart and checkout session
        $this->cartService->clearCart();
        Session::forget(self::SESSION_KEY);

        return [
            'success' => true,
            'order' => $order,
        ];
    }
}
