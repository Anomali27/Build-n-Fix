<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\UpdatePaymentStatusRequest;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $role = $user->role ?? 'customer';

        $statusFilter = (string) $request->get('status', 'all');
        $branchFilter = (string) $request->get('branch', 'all');
        $search = (string) $request->get('q', '');

        $baseQuery = Payment::query()
            ->when($role === 'customer', fn (Builder $q) => $q->forUser((int) $user->id));

        $payments = (clone $baseQuery)
            ->with(['order', 'branch', 'user'])
            ->when($role !== 'customer' && $branchFilter !== 'all', fn (Builder $q) => $q->forBranch($branchFilter))
            ->withStatus($statusFilter !== 'all' ? $statusFilter : null)
            ->search($search !== '' ? $search : null)
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $summary = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as paid_amount")
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->first();

        return view('payments.index', [
            'role' => $role,
            'payments' => $payments,
            'branches' => Branch::query()->orderBy('id')->get(),
            'statusFilter' => $statusFilter,
            'branchFilter' => $branchFilter,
            'search' => $search,
            'totalPaidAmount' => (int) ($summary?->paid_amount ?? 0),
            'pendingCount' => (int) ($summary?->pending_count ?? 0),
            'paidCount' => (int) ($summary?->paid_count ?? 0),
            'totalTransactions' => (int) ($summary?->total_count ?? 0),
        ]);
    }

    public function show(Request $request, string|int $payment): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $role = $user->role ?? 'customer';

        $selected = $this->findPaymentByIdentifier($payment);

        if (! $selected || ($role === 'customer' && (int) $selected->user_id !== (int) $user->id)) {
            return redirect()->route('payments.index')->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $payments = Payment::query()
            ->with(['order', 'branch', 'user'])
            ->when($role === 'customer', fn (Builder $q) => $q->forUser((int) $user->id))
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('payments.index', [
            'role' => $role,
            'payments' => $payments,
            'branches' => Branch::query()->orderBy('id')->get(),
            'selectedPayment' => $selected,
            'statusFilter' => 'all',
            'branchFilter' => 'all',
            'search' => '',
            'totalPaidAmount' => (int) $payments->where('status', 'paid')->sum('amount'),
            'pendingCount' => $payments->where('status', 'pending')->count(),
            'paidCount' => $payments->where('status', 'paid')->count(),
            'totalTransactions' => $payments->count(),
        ]);
    }

    public function update(UpdatePaymentStatusRequest $request, string|int $payment): RedirectResponse
    {
        $target = $this->findPaymentByIdentifier($payment);

        if (! $target) {
            return redirect()->route('payments.index')->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $newStatus = $request->validated()['status'];

        DB::transaction(function () use ($target, $newStatus): void {
            $target->update([
                'status' => $newStatus,
                'paid_at' => $newStatus === 'paid' ? ($target->paid_at ?? now()) : $target->paid_at,
            ]);

            if ($target->order) {
                $target->order->update(['payment_status' => $newStatus]);
            }
        });

        return redirect()->route('payments.index')
            ->with('success', 'Status pembayaran berhasil diperbarui menjadi '.ucfirst($newStatus).'.');
    }

    private function findPaymentByIdentifier(string|int $identifier): ?Payment
    {
        return Payment::query()
            ->with(['order', 'branch', 'user'])
            ->where('payment_code', (string) $identifier)
            ->when(is_numeric($identifier), fn (Builder $q) => $q->orWhere('id', (int) $identifier))
            ->orWhereHas('order', fn (Builder $o) => $o->where('order_number', (string) $identifier))
            ->first();
    }
}
