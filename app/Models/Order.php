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
    'discount',
    'voucher_code',
    'total',
])]
class Order extends Model
{
    public const STATUS_MENUNGGU_DIPROSES = 'menunggu_diproses';
    public const STATUS_SEDANG_DISIAPKAN = 'sedang_disiapkan';
    public const STATUS_SEDANG_DIKEMAS = 'sedang_dikemas';
    public const STATUS_SIAP_DIKIRIM = 'siap_dikirim';
    public const STATUS_SEDANG_DIANTAR = 'sedang_diantar';
    public const STATUS_SIAP_DIAMBIL = 'siap_diambil';
    public const STATUS_PESANAN_SELESAI = 'pesanan_selesai';
    public const STATUS_CANCELLED = 'cancelled';

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
            'discount' => 'integer',
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
     * Audit log history of order status changes.
     *
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Map any legacy or raw status code to its canonical normalized key.
     */
    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            'paid' => self::STATUS_MENUNGGU_DIPROSES,
            'proses' => self::STATUS_SEDANG_DISIAPKAN,
            'ready_to_pick_up', 'ready_to_pickup' => self::STATUS_SIAP_DIAMBIL,
            'on_delivery' => self::STATUS_SEDANG_DIANTAR,
            'order_completed', 'completed' => self::STATUS_PESANAN_SELESAI,
            default => strtolower(trim((string) $status)) ?: self::STATUS_MENUNGGU_DIPROSES,
        };
    }

    /**
     * Human-friendly label in Indonesian according to product specs.
     */
    public static function formatStatusLabel(?string $status): string
    {
        $normalized = self::normalizeStatus($status);

        return match ($normalized) {
            self::STATUS_MENUNGGU_DIPROSES => 'Menunggu Diproses',
            self::STATUS_SEDANG_DISIAPKAN => 'Sedang Disiapkan',
            self::STATUS_SEDANG_DIKEMAS => 'Sedang Dikemas',
            self::STATUS_SIAP_DIKIRIM => 'Siap Dikirim',
            self::STATUS_SEDANG_DIANTAR => 'Sedang Diantar',
            self::STATUS_SIAP_DIAMBIL => 'Siap Diambil',
            self::STATUS_PESANAN_SELESAI => 'Pesanan Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $normalized)),
        };
    }

    /**
     * Available status options for delivery fulfillment.
     *
     * @return array<string, string>
     */
    public static function deliveryStatuses(): array
    {
        return [
            self::STATUS_MENUNGGU_DIPROSES => 'Menunggu Diproses',
            self::STATUS_SEDANG_DISIAPKAN => 'Sedang Disiapkan',
            self::STATUS_SEDANG_DIKEMAS => 'Sedang Dikemas',
            self::STATUS_SIAP_DIKIRIM => 'Siap Dikirim',
            self::STATUS_SEDANG_DIANTAR => 'Sedang Diantar',
            self::STATUS_PESANAN_SELESAI => 'Pesanan Selesai',
        ];
    }

    /**
     * Available status options for pickup fulfillment.
     *
     * @return array<string, string>
     */
    public static function pickupStatuses(): array
    {
        return [
            self::STATUS_MENUNGGU_DIPROSES => 'Menunggu Diproses',
            self::STATUS_SEDANG_DISIAPKAN => 'Sedang Disiapkan',
            self::STATUS_SEDANG_DIKEMAS => 'Sedang Dikemas',
            self::STATUS_SIAP_DIAMBIL => 'Siap Diambil',
            self::STATUS_PESANAN_SELESAI => 'Pesanan Selesai / Sudah Diambil',
        ];
    }

    /**
     * Get valid status options for this order's fulfillment method.
     *
     * @return array<string, string>
     */
    public function allowedStatuses(): array
    {
        return strtolower((string) $this->fulfillment_method) === 'delivery'
            ? self::deliveryStatuses()
            : self::pickupStatuses();
    }

    /**
     * Human-readable order status label.
     *
     * @return Attribute<string, never>
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn (): string => self::formatStatusLabel($this->order_status));
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

        $branchName = $this->branch?->name ?? 'Serdam';
        $branchAddress = $this->branch?->address ?? $this->branch?->short_address ?? '';

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'branch_id' => $this->branch_id,
            'branch_name' => $branchName,
            'branch_address' => $branchAddress,
            'branch_city' => $this->branch?->city ?? 'Pontianak',
            'branch_phone' => $this->branch?->phone ?? '0561-123456',
            'fulfillment_method' => $this->fulfillment_method,
            'order_status' => self::normalizeStatus($this->order_status),
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
            'delivery_city' => $this->delivery_city,
            'delivery_postal_code' => $this->delivery_postal_code,
            'delivery_notes' => $this->delivery_notes,
            'subtotal' => $this->subtotal,
            'delivery_fee' => $this->delivery_fee,
            'discount' => $this->discount ?? 0,
            'voucher_code' => $this->voucher_code,
            'total' => $this->total,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s') ?? '',
            'created_at_formatted' => $this->created_at?->format('d M Y, H:i') ?? '',
            'timeline' => $this->trackingTimeline(),
            'allowed_statuses' => $this->allowedStatuses(),
            'status_histories' => $this->statusHistories->map(fn (OrderStatusHistory $h): array => [
                'id' => $h->id,
                'status_from' => $h->status_from,
                'status_from_label' => $h->status_from_label,
                'status_to' => $h->status_to,
                'status_to_label' => $h->status_to_label,
                'notes' => $h->notes,
                'actor_name' => $h->user?->name ?? 'Admin Build n Fix',
                'created_at' => $h->created_at?->format('Y-m-d H:i:s') ?? '',
                'time_formatted' => $h->created_at?->format('H:i') ?? '',
                'date_formatted' => $h->created_at?->format('d M Y') ?? '',
            ])->values()->all(),
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
        $currentStatus = self::normalizeStatus($this->order_status);
        $branchName = $this->branch?->name ?? 'Serdam';

        if ($method === 'pickup') {
            $steps = [
                [
                    'key' => self::STATUS_MENUNGGU_DIPROSES,
                    'label' => 'Menunggu Diproses',
                    'description' => 'Pesanan berhasil dibuat dan menunggu konfirmasi toko.',
                ],
                [
                    'key' => self::STATUS_SEDANG_DISIAPKAN,
                    'label' => 'Sedang Disiapkan',
                    'description' => 'Toko cabang '.$branchName.' mulai menyiapkan barang pesanan Anda.',
                ],
                [
                    'key' => self::STATUS_SEDANG_DIKEMAS,
                    'label' => 'Sedang Dikemas',
                    'description' => 'Barang pesanan sedang dikemas dan dipersiapkan untuk pengambilan.',
                ],
                [
                    'key' => self::STATUS_SIAP_DIAMBIL,
                    'label' => 'Siap Diambil',
                    'description' => 'Pesanan sudah siap diambil langsung di cabang '.$branchName.'.',
                ],
                [
                    'key' => self::STATUS_PESANAN_SELESAI,
                    'label' => 'Pesanan Selesai',
                    'description' => 'Pesanan telah diambil oleh customer dan transaksi selesai.',
                ],
            ];
            $statusOrder = [
                self::STATUS_MENUNGGU_DIPROSES,
                self::STATUS_SEDANG_DISIAPKAN,
                self::STATUS_SEDANG_DIKEMAS,
                self::STATUS_SIAP_DIAMBIL,
                self::STATUS_PESANAN_SELESAI,
            ];
        } else {
            $steps = [
                [
                    'key' => self::STATUS_MENUNGGU_DIPROSES,
                    'label' => 'Menunggu Diproses',
                    'description' => 'Pesanan berhasil dibuat dan menunggu konfirmasi pengiriman toko.',
                ],
                [
                    'key' => self::STATUS_SEDANG_DISIAPKAN,
                    'label' => 'Sedang Disiapkan',
                    'description' => 'Toko cabang '.$branchName.' sedang menyiapkan barang pesanan.',
                ],
                [
                    'key' => self::STATUS_SEDANG_DIKEMAS,
                    'label' => 'Sedang Dikemas',
                    'description' => 'Barang pesanan sedang dikemas dengan standar pengiriman aman.',
                ],
                [
                    'key' => self::STATUS_SIAP_DIKIRIM,
                    'label' => 'Siap Dikirim',
                    'description' => 'Barang pesanan telah siap dijemput oleh armada pengantaran.',
                ],
                [
                    'key' => self::STATUS_SEDANG_DIANTAR,
                    'label' => 'Sedang Diantar',
                    'description' => 'Pesanan dalam perjalanan pengantaran menuju alamat penerima.',
                ],
                [
                    'key' => self::STATUS_PESANAN_SELESAI,
                    'label' => 'Pesanan Selesai',
                    'description' => 'Pesanan telah tiba di alamat tujuan dan diterima dengan baik.',
                ],
            ];
            $statusOrder = [
                self::STATUS_MENUNGGU_DIPROSES,
                self::STATUS_SEDANG_DISIAPKAN,
                self::STATUS_SEDANG_DIKEMAS,
                self::STATUS_SIAP_DIKIRIM,
                self::STATUS_SEDANG_DIANTAR,
                self::STATUS_PESANAN_SELESAI,
            ];
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

        $normalized = self::normalizeStatus($clean);

        match ($normalized) {
            self::STATUS_PESANAN_SELESAI => $query->whereIn('order_status', ['pesanan_selesai', 'order_completed', 'completed']),
            self::STATUS_MENUNGGU_DIPROSES => $query->whereIn('order_status', ['menunggu_diproses', 'paid']),
            self::STATUS_SEDANG_DISIAPKAN => $query->whereIn('order_status', ['sedang_disiapkan', 'proses']),
            self::STATUS_SIAP_DIAMBIL => $query->whereIn('order_status', ['siap_diambil', 'ready_to_pick_up']),
            self::STATUS_SEDANG_DIANTAR => $query->whereIn('order_status', ['sedang_diantar', 'on_delivery']),
            default => $query->where('order_status', $clean),
        };
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
