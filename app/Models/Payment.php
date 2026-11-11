<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'user_id',
    'branch_id',
    'payment_code',
    'payment_method',
    'amount',
    'status',
    'paid_at',
    'proof_url',
])]
class Payment extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Order settled by this payment.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Customer associated with this payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Branch associated with this payment.
     *
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Human-readable payment status label in Indonesian.
     *
     * @return Attribute<string, never>
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn (): string => match (strtolower((string) $this->status)) {
            'paid' => 'Lunas (Verified)',
            'pending' => 'Menunggu Pembayaran',
            'failed' => 'Gagal',
            'refunded' => 'Dikembalikan',
            default => ucfirst((string) $this->status),
        });
    }

    /**
     * Scope: filter payments owned by a specific user.
     *
     * @param  Builder<Payment>  $query
     */
    public function scopeForUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Scope: filter payments by branch ID or branch name.
     *
     * @param  Builder<Payment>  $query
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
     * Scope: filter payments by status.
     *
     * @param  Builder<Payment>  $query
     */
    public function scopeWithStatus(Builder $query, ?string $status): void
    {
        $clean = trim((string) $status);
        if ($clean === '' || strtolower($clean) === 'all') {
            return;
        }

        $query->where('status', $clean);
    }

    /**
     * Scope: search payments by payment code, order number, method, customer, or branch.
     *
     * @param  Builder<Payment>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $clean = trim((string) $term);
        if ($clean === '') {
            return;
        }

        $query->where(function (Builder $q) use ($clean): void {
            $q->where('payment_code', 'like', '%'.$clean.'%')
                ->orWhere('payment_method', 'like', '%'.$clean.'%')
                ->orWhereHas('order', function (Builder $o) use ($clean): void {
                    $o->where('order_number', 'like', '%'.$clean.'%')
                        ->orWhere('customer_name', 'like', '%'.$clean.'%')
                        ->orWhere('customer_email', 'like', '%'.$clean.'%');
                })
                ->orWhereHas('branch', fn (Builder $b) => $b->where('name', 'like', '%'.$clean.'%'));
        });
    }
}
