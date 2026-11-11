<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable([
    'order_number',
    'user_id',
    'branch_id',
    'fulfillment_method',
    'order_status',
    'payment_status',
    'customer_name',
    'customer_email',
    'customer_phone',
    'recipient_name',
    'recipient_phone',
    'delivery_address',
    'delivery_city',
    'delivery_postal_code',
    'delivery_notes',
    'subtotal',
    'delivery_fee',
    'total',
])]
class Order extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'delivery_fee' => 'integer',
            'total' => 'integer',
        ];
    }

    /**
     * Customer who placed this order.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Fulfillment branch for this order.
     *
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Line items belonging to this order.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Payment transactions associated with this order.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Latest payment transaction for this order.
     *
     * @return HasOne<Payment, $this>
     */
    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Human-readable order status label.
     *
     * @return Attribute<string, never>
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn (): string => match (strtolower((string) $this->order_status)) {
            'paid' => 'Paid',
            'ready_to_pick_up' => 'Ready to Pick Up',
            'proses' => 'Proses',
            'on_delivery' => 'On Delivery',
            'order_completed', 'completed' => 'Order Completed',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', (string) $this->order_status)),
        });
    }

    /**
     * Customer-facing payment status text in Indonesian.
     *
     * @return Attribute<string, never>
     */
    protected function paymentBadgeLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->payment_status === 'paid' ? 'Sudah Bayar' : 'Belum Bayar');
    }

    /**
     * Serialize this order into a structured array for views and Alpine modals.
     *
     * @return array<string, mixed>
     */
    public function toDisplayArray(): array
    {
        $fullAddress = trim(implode(', ', array_filter([
            $this->delivery_address,
            $this->delivery_city,
            $this->delivery_postal_code,
        ])));

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'branch_id' => $this->branch_id,
            'branch_name' => $this->branch?->name ?? 'Serdam',
            'branch_address' => $this->branch?->address ?? $this->branch?->short_address ?? '',
            'fulfillment_method' => $this->fulfillment_method,
            'order_status' => $this->order_status,
            'status_label' => $this->status_label,
            'payment_status' => $this->payment_status,
            'payment_badge_label' => $this->payment_badge_label,
            'payment_code' => $this->latestPayment?->payment_code,
            'payment_method' => $this->latestPayment?->payment_method ?? 'Build n Fix Payment Simulation',
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'recipient_name' => $this->recipient_name,
            'recipient_phone' => $this->recipient_phone,
            'delivery_address' => $fullAddress !== '' ? $fullAddress : null,
            'delivery_notes' => $this->delivery_notes,
            'subtotal' => $this->subtotal,
            'delivery_fee' => $this->delivery_fee,
            'total' => $this->total,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s') ?? '',
            'created_at_formatted' => $this->created_at?->format('d M Y, H:i') ?? '',
            'timeline' => $this->trackingTimeline(),
            'items' => $this->items->map(function (OrderItem $item): array {
                $img = (string) ($item->product_image ?? '');
                $resolvedImage = $img === ''
                    ? 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600&auto=format&fit=crop&q=80'
                    : (Str::startsWith($img, ['http://', 'https://', '/']) ? $img : '/'.$img);

                return [
                    'product_id' => $item->product_id,
                    'sku' => $item->product_sku,
                    'product_name' => $item->product_name,
                    'brand' => $item->product_brand ?? 'Build n Fix',
                    'unit' => $item->product_unit ?? 'Unit',
                    'image' => $resolvedImage,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ];
            })->values()->all(),
        ];
    }

    /**
     * Build tracking timeline steps based on fulfillment method and current status.
     *
     * @return array<int, array{key: string, label: string, description: string, completed: bool, current: bool}>
     */
    public function trackingTimeline(): array
    {
        $method = strtolower((string) $this->fulfillment_method);
        $currentStatus = strtolower((string) $this->order_status);
        $branchName = $this->branch?->name ?? 'Serdam';

        if ($currentStatus === 'completed') {
            $currentStatus = 'order_completed';
        }

        if ($method === 'pickup') {
            $steps = [
                [
                    'key' => 'paid',
                    'label' => 'Payment Confirmed',
                    'description' => 'Pembayaran berhasil dan pesanan sedang diproses.',
                ],
                [
                    'key' => 'ready_to_pick_up',
                    'label' => 'Ready to Pick Up',
                    'description' => 'Pesanan sudah siap diambil di cabang '.$branchName.'.',
                ],
                [
                    'key' => 'order_completed',
                    'label' => 'Order Completed',
                    'description' => 'Pesanan telah diambil dan selesai.',
                ],
            ];
            $statusOrder = ['paid', 'ready_to_pick_up', 'order_completed'];
        } else {
            $steps = [
                [
                    'key' => 'paid',
                    'label' => 'Payment Confirmed',
                    'description' => 'Pembayaran berhasil dan pesanan sedang diproses.',
                ],
                [
                    'key' => 'proses',
                    'label' => 'Order Processing',
                    'description' => 'Pesanan sedang dipersiapkan untuk pengiriman.',
                ],
                [
                    'key' => 'on_delivery',
                    'label' => 'On Delivery',
                    'description' => 'Pesanan sedang dalam perjalanan ke alamat tujuan.',
                ],
                [
                    'key' => 'order_completed',
                    'label' => 'Order Completed',
                    'description' => 'Pesanan telah diterima dan selesai.',
                ],
            ];
            $statusOrder = ['paid', 'proses', 'on_delivery', 'order_completed'];
        }

        $currentIndex = array_search($currentStatus, $statusOrder, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        foreach ($steps as $index => &$step) {
            $step['completed'] = $index <= $currentIndex;
            $step['current'] = $index === $currentIndex;
        }

        return $steps;
    }

    /**
     * Scope: filter orders owned by a specific user.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeForUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Scope: filter orders by branch ID or branch name.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeForBranch(Builder $query, string|int|null $branch): void
    {
        $clean = trim((string) $branch);
        if ($clean === '' || strtolower($clean) === 'all') {
            return;
        }

        $query->where(function (Builder $q) use ($clean): void {
            if (is_numeric($clean)) {
                $q->where('branch_id', (int) $clean);
            }

            $q->orWhereHas('branch', fn (Builder $b) => $b->where('name', $clean));
        });
    }

    /**
     * Scope: filter orders by order status.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeWithStatus(Builder $query, ?string $status): void
    {
        $clean = trim((string) $status);
        if ($clean === '' || strtolower($clean) === 'all') {
            return;
        }

        if ($clean === 'completed' || $clean === 'order_completed') {
            $query->whereIn('order_status', ['order_completed', 'completed']);

            return;
        }

        $query->where('order_status', $clean);
    }

    /**
     * Scope: search orders by order number, customer info, branch, or payment method.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $clean = trim((string) $term);
        if ($clean === '') {
            return;
        }

        $query->where(function (Builder $q) use ($clean): void {
            $q->where('order_number', 'like', '%'.$clean.'%')
                ->orWhere('customer_name', 'like', '%'.$clean.'%')
                ->orWhere('customer_email', 'like', '%'.$clean.'%')
                ->orWhereHas('branch', fn (Builder $b) => $b->where('name', 'like', '%'.$clean.'%'))
                ->orWhereHas('payments', fn (Builder $p) => $p->where('payment_method', 'like', '%'.$clean.'%'));
        });
    }

    /**
     * Build Eloquent sales analytics report across branches and periods.
     *
     * @return array<string, mixed>
     */
    public static function buildSalesReport(string $branch = 'all', string $period = 'this_year'): array
    {
        $now = Carbon::now();

        [$startDate, $endDate] = match ($period) {
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            default => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
        };

        $branches = Branch::query()->orderBy('id')->get();
        $selectedBranchModel = $branch !== 'all'
            ? $branches->first(fn (Branch $b): bool => strcasecmp($b->name, $branch) === 0 || (string) $b->id === $branch)
            : null;

        /** @var Collection<int, Order> $allPeriodOrders */
        $allPeriodOrders = static::query()
            ->with('branch')
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $scopedOrders = $selectedBranchModel
            ? $allPeriodOrders->where('branch_id', $selectedBranchModel->id)->values()
            : $allPeriodOrders;

        $totalRevenueAllBranches = (int) $allPeriodOrders->sum('total');
        $branchSummary = [];
        $topBranchName = '-';
        $topBranchRevenue = -1;
        $topBranchShare = 0.0;

        foreach ($branches as $b) {
            $bOrders = $allPeriodOrders->where('branch_id', $b->id);
            $rev = (int) $bOrders->sum('total');
            $tx = $bOrders->count();
            $share = $totalRevenueAllBranches > 0
                ? round(($rev / $totalRevenueAllBranches) * 100, 1)
                : 0.0;

            if ($rev > $topBranchRevenue) {
                $topBranchRevenue = $rev;
                $topBranchName = 'Cabang '.$b->name;
                $topBranchShare = $share;
            }

            $branchSummary[$b->name] = [
                'branch_id' => $b->id,
                'branch_name' => 'Cabang '.$b->name,
                'short_name' => $b->name,
                'revenue' => $rev,
                'transactions' => $tx,
                'share_percentage' => $share,
            ];
        }

        $totalRevenue = (int) $scopedOrders->sum('total');
        $totalTransactions = $scopedOrders->count();
        $avgOrderValue = $totalTransactions > 0 ? (int) round($totalRevenue / $totalTransactions) : 0;

        // Monthly trend across months 1..current month in target year
        $yearStart = $now->copy()->startOfYear();
        $yearEnd = $now->copy()->endOfYear();
        $yearOrders = static::query()
            ->where('payment_status', 'paid')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->when($selectedBranchModel, fn (Builder $q) => $q->where('branch_id', $selectedBranchModel->id))
            ->get(['branch_id', 'total', 'created_at']);

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $maxMonth = $now->month;
        $months = array_slice($monthNames, 0, $maxMonth);

        $palette = ['#F97316', '#8B5CF6', '#06B6D4', '#10B981', '#F43F5E'];
        $fillPalette = [
            'rgba(249, 115, 22, 0.10)',
            'rgba(139, 92, 246, 0.08)',
            'rgba(6, 182, 212, 0.08)',
            'rgba(16, 185, 129, 0.08)',
            'rgba(244, 63, 94, 0.08)',
        ];

        $chartBranches = $selectedBranchModel ? collect([$selectedBranchModel]) : $branches;
        $monthlyDatasets = [];
        $dailyDatasets = [];

        foreach ($chartBranches->values() as $idx => $b) {
            $bMonthTotals = array_fill(1, $maxMonth, 0);
            foreach ($yearOrders->where('branch_id', $b->id) as $ord) {
                $m = (int) $ord->created_at->month;
                if ($m >= 1 && $m <= $maxMonth) {
                    $bMonthTotals[$m] += (int) $ord->total;
                }
            }

            $color = $palette[$idx % count($palette)];
            $monthlyDatasets[] = [
                'label' => 'Cabang '.$b->name,
                'data' => array_values($bMonthTotals),
                'borderColor' => $color,
                'backgroundColor' => $fillPalette[$idx % count($fillPalette)],
            ];
        }

        // Daily 7-day window anchored to latest matching paid order (or end of period/now)
        $latestScopedCreatedAt = $scopedOrders->last()?->created_at?->copy();
        $anchorEnd = $latestScopedCreatedAt ?? ($endDate->greaterThan($now) ? $now->copy() : $endDate->copy());
        $dailyDates = [];
        $dailyLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = $anchorEnd->copy()->subDays($i);
            $dailyDates[] = $day->format('Y-m-d');
            $dailyLabels[] = $day->format('d M');
        }

        $dailyRevenueCombined = array_fill(0, 7, 0);
        foreach ($chartBranches->values() as $idx => $b) {
            $series = [];
            foreach ($dailyDates as $dIdx => $dateKey) {
                $dayTotal = (int) $scopedOrders
                    ->where('branch_id', $b->id)
                    ->filter(fn (Order $o): bool => $o->created_at?->format('Y-m-d') === $dateKey)
                    ->sum('total');
                $series[] = $dayTotal;
                $dailyRevenueCombined[$dIdx] += $dayTotal;
            }

            $dailyDatasets[] = [
                'label' => $b->name,
                'data' => $series,
                'backgroundColor' => $palette[$idx % count($palette)],
            ];
        }

        $peakDayAmount = 0;
        $peakDayLabel = '-';
        foreach ($dailyRevenueCombined as $dIdx => $amt) {
            if ($amt >= $peakDayAmount && $amt > 0) {
                $peakDayAmount = $amt;
                $peakDayLabel = $dailyLabels[$dIdx];
            }
        }

        // Top 5 selling products aggregated from order_items
        $topProducts = OrderItem::query()
            ->select([
                'order_items.product_name',
                'order_items.product_unit',
                DB::raw('MAX(categories.name) as category_name'),
                DB::raw('SUM(order_items.quantity) as sold_qty'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
            ])
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.order_status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->when($selectedBranchModel, fn ($q) => $q->where('orders.branch_id', $selectedBranchModel->id))
            ->groupBy('order_items.product_name', 'order_items.product_unit')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->product_name,
                'category' => $row->category_name ?? 'Material Bangunan',
                'unit' => $row->product_unit ?? 'Unit',
                'sold_qty' => (int) $row->sold_qty,
                'revenue' => (int) $row->revenue,
            ])
            ->all();

        $periodLabel = match ($period) {
            'this_month' => 'Bulan '.$now->format('M Y'),
            'last_month' => 'Bulan '.$now->copy()->subMonth()->format('M Y'),
            default => 'Tahun '.$now->year,
        };

        return [
            'selected_branch' => $branch,
            'selected_period' => $period,
            'period_label' => $periodLabel,
            'branches' => $branches,
            'months' => $months,
            'daily_labels' => $dailyLabels,
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'avg_order_value' => $avgOrderValue,
            'top_branch_name' => $topBranchName,
            'top_branch_share' => $topBranchShare,
            'peak_day_label' => $peakDayLabel,
            'peak_day_amount' => $peakDayAmount,
            'branch_summary' => $branchSummary,
            'monthly_datasets' => $monthlyDatasets,
            'daily_datasets' => $dailyDatasets,
            'top_products' => $topProducts,
        ];
    }
}
