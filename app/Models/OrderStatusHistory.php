<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'user_id',
    'status_from',
    'status_to',
    'notes',
])]
class OrderStatusHistory extends Model
{
    /**
     * Parent order for this status history entry.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * User/admin who performed this status change (nullable).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Formatted label of status_to in Indonesian.
     *
     * @return Attribute<string, never>
     */
    protected function statusToLabel(): Attribute
    {
        return Attribute::get(fn (): string => Order::formatStatusLabel((string) $this->status_to));
    }

    /**
     * Formatted label of status_from in Indonesian.
     *
     * @return Attribute<string|null, never>
     */
    protected function statusFromLabel(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->status_from ? Order::formatStatusLabel((string) $this->status_from) : null);
    }
}
