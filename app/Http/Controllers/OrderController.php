<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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

        if ($role === 'customer') {
            $customerData = $this->buildCustomerOrderLists((int) $user->id, $filter, $search);

            return view('orders.index', array_merge($customerData, [
                'role' => $role,
                'activeTab' => $tab,
                'activeFilter' => $filter,
                'search' => $search,
                'branchFilter' => $branchFilter,
                'statusFilter' => $statusFilter,
                'selectedOrder' => null,
                'managementOrders' => [],
                'branches' => collect(),
            ]));
        }

        $managementOrders = Order::query()
            ->with(['branch', 'items', 'latestPayment', 'statusHistories.user'])
            ->forBranch($branchFilter !== 'all' ? $branchFilter : null)
            ->withStatus($statusFilter !== 'all' ? $statusFilter : null)
            ->search($search !== '' ? $search : null)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Order $order): array => $order->toDisplayArray())
            ->all();

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
            'selectedOrder' => null,
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
            abort(404, 'Pesanan tidak ditemukan atau Anda tidak memiliki akses untuk melihat pesanan ini.');
        }

        $orderData = $found->toDisplayArray();

        return view('orders.show', [
            'role' => $role,
            'order' => $orderData,
            'rawOrder' => $found,
        ]);
    }

    public function statusJson(Request $request, string $order): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $role = $user->role ?? 'customer';

        $found = $this->findOrderByIdentifier($order);

        if (! $found || ($role === 'customer' && (int) $found->user_id !== (int) $user->id)) {
            return response()->json(['error' => 'Pesanan tidak ditemukan atau akses ditolak.'], 404);
        }

        $orderData = $found->toDisplayArray();

        return response()->json([
            'order_number' => $found->order_number,
            'order_status' => $orderData['order_status'],
            'status_label' => $orderData['status_label'],
            'payment_status' => $orderData['payment_status'],
            'payment_badge_label' => $orderData['payment_badge_label'],
            'timeline' => $orderData['timeline'],
            'status_histories' => $orderData['status_histories'],
        ]);
    }

    public function update(UpdateOrderStatusRequest $request, string $order): RedirectResponse
    {
        $target = $this->findOrderByIdentifier($order);

        if (! $target) {
            abort(404, 'Pesanan tidak ditemukan.');
        }

        $validated = $request->validated();
        $oldStatus = $target->order_status;
        $newStatus = $validated['order_status'];

        DB::transaction(function () use ($target, $validated, $oldStatus, $newStatus, $request): void {
            $updatePayload = ['order_status' => $newStatus];

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

            if ($oldStatus !== $newStatus) {
                $target->statusHistories()->create([
                    'user_id' => $request->user()?->id,
                    'status_from' => $oldStatus,
                    'status_to' => $newStatus,
                    'notes' => $validated['notes'] ?? ('Status diperbarui menjadi '.Order::formatStatusLabel($newStatus)),
                ]);
            }
        });

        if ($request->has('redirect_to_show')) {
            return redirect()->route('orders.show', $target->order_number)
                ->with('success', 'Status pesanan '.$target->order_number.' berhasil diperbarui menjadi '.Order::formatStatusLabel($newStatus).'.');
        }

        return redirect()->back()
            ->with('success', 'Status pesanan '.$target->order_number.' berhasil diperbarui menjadi '.Order::formatStatusLabel($newStatus).'.');
    }

    /**
     * Fetch and partition customer order tabs from MySQL in a single query.
     *
     * @return array{allOrders: array<int, array<string, mixed>>, successOrders: array<int, array<string, mixed>>, trackingOrders: array<int, array<string, mixed>>, historyOrders: array<int, array<string, mixed>>}
     */
    private function buildCustomerOrderLists(int $userId, string $filter = 'all', string $search = ''): array
    {
        $orders = Order::query()
            ->with(['branch', 'items', 'latestPayment', 'statusHistories.user'])
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

        $completedStatuses = ['pesanan_selesai', 'order_completed', 'completed'];

        $filteredAll = match (strtolower($filter)) {
            'pickup' => array_values(array_filter($filteredAll, fn (array $o): bool => $o['fulfillment_method'] === 'pickup')),
            'delivery' => array_values(array_filter($filteredAll, fn (array $o): bool => $o['fulfillment_method'] === 'delivery')),
            'active' => array_values(array_filter($filteredAll, fn (array $o): bool => ! in_array($o['order_status'], array_merge($completedStatuses, ['cancelled', 'dibatalkan']), true))),
            'completed' => array_values(array_filter($filteredAll, fn (array $o): bool => in_array($o['order_status'], $completedStatuses, true))),
            default => $filteredAll,
        };

        $successOrders = array_values(array_filter(
            $orders,
            fn (array $o): bool => $o['payment_status'] === 'paid'
        ));

        $trackingOrders = array_values(array_filter(
            $orders,
            fn (array $o): bool => ! in_array($o['order_status'], array_merge($completedStatuses, ['cancelled', 'dibatalkan']), true)
        ));

        $historyOrders = array_values(array_filter(
            $orders,
            fn (array $o): bool => in_array($o['order_status'], $completedStatuses, true)
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
            ->with(['branch', 'items', 'latestPayment', 'statusHistories.user'])
            ->where('order_number', $identifier)
            ->when(is_numeric($identifier), fn ($q) => $q->orWhere('id', (int) $identifier))
            ->first();
    }
}
