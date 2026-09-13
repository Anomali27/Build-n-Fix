<?php

namespace App\Services;

use App\Repositories\BranchStockRepository;
use Illuminate\Support\Facades\Session;

class PaymentService
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected CartService $cartService,
        protected OrderService $orderService
    ) {}

    /**
     * Validate whether checkout session data exists and is ready for payment.
     */
    public function validatePaymentSession(): bool
    {
        $checkoutData = $this->checkoutService->getCheckoutData();

        return ($checkoutData['cart']['item_count'] ?? 0) > 0 && ($checkoutData['is_valid'] ?? false);
    }

    /**
     * Generate unique payment reference in format PAY-YYYYMMDD-XXX.
     */
    public function generatePaymentReference(): string
    {
        $dateStr = date('Ymd');
        $counter = rand(100, 999);

        return "PAY-{$dateStr}-{$counter}";
    }

    /**
     * Process simulated payment.
     *
     * @param  array<string, mixed>  $paymentInput
     * @return array{success: bool, order?: array<string, mixed>, reference?: string, message?: string}
     */
    public function processMockPayment(array $paymentInput): array
    {
        $checkoutData = $this->checkoutService->getCheckoutData();

        if (! $this->validatePaymentSession()) {
            return [
                'success' => false,
                'message' => 'Payment session is invalid or cart is empty.',
            ];
        }

        $simulationOutcome = strtolower($paymentInput['simulation_outcome'] ?? 'success');
        $paymentMethod = $paymentInput['payment_method'] ?? 'Build n Fix Payment Simulation';

        // IF PAYMENT FAILS
        if ($simulationOutcome === 'failed') {
            return [
                'success' => false,
                'message' => 'Your payment could not be completed. Please try again.',
            ];
        }

        // IF PAYMENT SUCCEEDS
        $paymentReference = $this->generatePaymentReference();
        $branchId = (int) $checkoutData['branch']['id'];

        // 1. Create order
        $order = $this->orderService->createOrderFromCheckout(
            $checkoutData,
            $paymentReference,
            $paymentMethod
        );

        // 2. Decrement stock for the specific branch ONLY
        foreach ($checkoutData['cart']['items'] as $item) {
            $productId = (int) $item['product_id'];
            $qty = (int) $item['quantity'];

            BranchStockRepository::decrementStock($productId, $branchId, $qty);
        }

        // 3. Clear Cart & Checkout session
        $this->cartService->clearCart();
        Session::forget('checkout');

        return [
            'success' => true,
            'order' => $order,
            'reference' => $paymentReference,
        ];
    }
}
