<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $role = $user->role ?? 'customer';

        $tab = (string) $request->query('tab', 'all');
        $filter = (string) $request->query('filter', 'all');
        $search = (string) $request->query('search', $request->query('q', ''));
        $branchFilter = (string) $request->query('branch', 'all');
        $statusFilter = (string) $request->query('status', 'all');
        $selectedOrderIdentifier = $request->query('order');

        if ($role === 'customer') {
            $customerData = $this->buildCustomerOrderLists((int) $user->id, $filter, $search);

            $selectedOrder = null;
            if ($selectedOrderIdentifier !== null && $selectedOrderIdentifier !== '') {
                $selectedOrder = Order::query()
                    ->with(['branch', 'items', 'latestPayment'])
                    ->forUser((int) $user->id)
                    ->where('order_number', (string) $selectedOrderIdentifier)
                    ->first()
                    ?->toDisplayArray();
            }

            return view('orders.index', array_merge($customerData, [
                'role' => $role,
                'activeTab' => $tab,
                'activeFilter' => $filter,
                'search' => $search,
                'branchFilter' => $branchFilter,
                'statusFilter' => $statusFilter,
                'selectedOrder' => $selectedOrder,
                'managementOrders' => [],
                'branches' => collect(),
            ]));
        }

        $managementOrders = Order::query()
            ->with(['branch', 'items', 'latestPayment'])
            ->forBranch($branchFilter !== 'all' ? $branchFilter : null)
            ->withStatus($statusFilter !== 'all' ? $statusFilter : null)
            ->search($search !== '' ? $search : null)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Order $order): array => $order->toDisplayArray())
            ->all();

        $selectedOrder = null;
        if ($selectedOrderIdentifier !== null && $selectedOrderIdentifier !== '') {
            $selectedOrder = $this->findOrderByIdentifier((string) $selectedOrderIdentifier)?->toDisplayArray();
        }

        return view('orders.index', [
            'role' => $role,
            'activeTab' => $tab,
            'activeFilter' => $filter,
            'search' => $search,
            'branchFilter' => $branchFilter,
            'statusFilter' => $statusFilter,
            'allOrders' => [],
            'successOrders' => [],
            'trackingOrders' => [],
            'historyOrders' => [],
            'selectedOrder' => $selectedOrder,
            'managementOrders' => $managementOrders,
            'branches' => Branch::query()->orderBy('id')->get(),
        ]);
    }

    public function show(Request $request, string $order): View
    {
        /** @var User $user */
        $user = $request->user();
        $role = $user->role ?? 'customer';

        $found = $this->findOrderByIdentifier($order);

        if (! $found || ($role === 'customer' && (int) $found->user_id !== (int) $user->id)) {
            abort(404, 'Pesanan tidak ditemukan atau Anda tidak memiliki akses.');
        }

        if ($role === 'customer') {
            $customerData = $this->buildCustomerOrderLists((int) $user->id);

            return view('orders.index', array_merge($customerData, [
                'role' => $role,
                'activeTab' => 'all',
                'activeFilter' => 'all',
                'search' => '',
                'branchFilter' => 'all',
                'statusFilter' => 'all',
                'selectedOrder' => $found->toDisplayArray(),
                'managementOrders' => [],
                'branches' => collect(),
            ]));
        }

        $managementOrders = Order::query()
            ->with(['branch', 'items', 'latestPayment'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Order $o): array => $o->toDisplayArray())
            ->all();

        return view('orders.index', [
            'role' => $role,
            'activeTab' => 'all',
            'activeFilter' => 'all',
            'search' => '',
            'branchFilter' => 'all',
            'statusFilter' => 'all',
            'allOrders' => [],
            'successOrders' => [],
            'trackingOrders' => [],
            'historyOrders' => [],
            'selectedOrder' => $found->toDisplayArray(),
            'managementOrders' => $managementOrders,
            'branches' => Branch::query()->orderBy('id')->get(),
        ]);
    }

    public function update(UpdateOrderStatusRequest $request, string $order): RedirectResponse
    {
        $target = $this->findOrderByIdentifier($order);

        if (! $target) {
            abort(404, 'Pesanan tidak ditemukan.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($target, $validated): void {
            $updatePayload = ['order_status' => $validated['order_status']];

            if (! empty($validated['payment_status'])) {
                $updatePayload['payment_status'] = $validated['payment_status'];

                if ($target->latestPayment) {
                    $target->latestPayment->update([
                        'status' => $validated['payment_status'],
                        'paid_at' => $validated['payment_status'] === 'paid'
                            ? ($target->latestPayment->paid_at ?? now())
                            : $target->latestPayment->paid_at,
                    ]);
                }
            }

            $target->update($updatePayload);
        });

        return redirect()->route('orders.index')
            ->with('success', 'Status pesanan '.$target->order_number.' berhasil diperbarui.');
    }

    /**
     * Fetch and partition customer order tabs from MySQL in a single query.
     *
     * @return array{allOrders: array<int, array<string, mixed>>, successOrders: array<int, array<string, mixed>>, trackingOrders: array<int, array<string, mixed>>, historyOrders: array<int, array<string, mixed>>}
     */
    private function buildCustomerOrderLists(int $userId, string $filter = 'all', string $search = ''): array
    {
        $orders = Order::query()
            ->with(['branch', 'items', 'latestPayment'])
            ->forUser($userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Order $order): array => $order->toDisplayArray())
            ->all();

        $filteredAll = $orders;
        if (trim($search) !== '') {
            $q = strtolower(trim($search));
            $filteredAll = array_values(array_filter(
                $filteredAll,
                fn (array $o): bool => str_contains(strtolower($o['order_number']), $q)
                    || str_contains(strtolower($o['branch_name']), $q)
            ));
        }

        $filteredAll = match (strtolower($filter)) {
            'pickup' => array_values(array_filter($filteredAll, fn (array $o): bool => $o['fulfillment_method'] === 'pickup')),
            'delivery' => array_values(array_filter($filteredAll, fn (array $o): bool => $o['fulfillment_method'] === 'delivery')),
            'active' => array_values(array_filter($filteredAll, fn (array $o): bool => ! in_array($o['order_status'], ['order_completed', 'completed', 'cancelled'], true))),
            'completed' => array_values(array_filter($filteredAll, fn (array $o): bool => in_array($o['order_status'], ['order_completed', 'completed'], true))),
            default => $filteredAll,
        };

        $successOrders = array_values(array_filter(
            $orders,
            fn (array $o): bool => $o['payment_status'] === 'paid'
        ));

        $trackingOrders = array_values(array_filter(
            $orders,
            fn (array $o): bool => ! in_array($o['order_status'], ['order_completed', 'completed', 'cancelled'], true)
        ));

        $historyOrders = array_values(array_filter(
            $orders,
            fn (array $o): bool => in_array($o['order_status'], ['order_completed', 'completed'], true)
        ));

        return [
            'allOrders' => $filteredAll,
            'successOrders' => $successOrders,
            'trackingOrders' => $trackingOrders,
            'historyOrders' => $historyOrders,
        ];
    }

    private function findOrderByIdentifier(string $identifier): ?Order
    {
        return Order::query()
            ->with(['branch', 'items', 'latestPayment'])
            ->where('order_number', $identifier)
            ->when(is_numeric($identifier), fn ($q) => $q->orWhere('id', (int) $identifier))
            ->first();
    }
}
