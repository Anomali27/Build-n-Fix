<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Check if customer is authenticated via session or auth guard.
     */
    protected function isAuthenticated(): bool
    {
        return Session::has('user') || auth()->check();
    }

    /**
     * Display the Checkout index page.
     */
    public function index(): View|RedirectResponse
    {
        if (! $this->isAuthenticated()) {
            return redirect()->route('login')->with('info', 'Please log in to complete your checkout.');
        }

        $checkoutData = $this->checkoutService->getCheckoutData();

        if (($checkoutData['cart']['item_count'] ?? 0) === 0) {
            return redirect()->route('cart.index')->with('info', 'Your cart is empty. Please add products before checking out.');
        }

        return view('checkout.index', compact('checkoutData'));
    }

    /**
     * Save checkout selections and validate before proceeding to payment.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $this->isAuthenticated()) {
            return redirect()->route('login')->with('info', 'Please log in to complete your checkout.');
        }

        $result = $this->checkoutService->preparePayment($request->all());

        if (! $result['success']) {
            if (is_array($result['errors'])) {
                return redirect()->back()->withInput()->withErrors($result['errors']);
            }

            return redirect()->back()->withInput()->with('error', 'Checkout validation failed.');
        }

        return redirect()->route('checkout.payment');
    }

    /**
     * Display the Payment Simulation page.
     */
    public function payment(): View|RedirectResponse
    {
        if (! $this->isAuthenticated()) {
            return redirect()->route('login')->with('info', 'Please log in to proceed to payment.');
        }

        $checkoutData = $this->checkoutService->getCheckoutData();

        if (($checkoutData['cart']['item_count'] ?? 0) === 0) {
            return redirect()->route('cart.index')->with('info', 'Your cart is empty.');
        }

        return view('checkout.payment', compact('checkoutData'));
    }

    /**
     * Process payment simulation and create the order.
     */
    public function processPayment(Request $request): RedirectResponse
    {
        if (! $this->isAuthenticated()) {
            return redirect()->route('login')->with('info', 'Please log in to process payment.');
        }

        $result = $this->paymentService->processMockPayment($request->all());

        if (! $result['success']) {
            return redirect()->route('checkout.payment')
                ->withInput()
                ->with('payment_failed', true)
                ->with('error', $result['message'] ?? 'Your payment could not be completed. Please try again.');
        }

        $order = $result['order'];

        return redirect()->route('orders.index', ['tab' => 'success'])
            ->with('payment_success', true)
            ->with('latest_order', $order)
            ->with('success', 'Payment Successful! Order placed successfully.');
    }
}
