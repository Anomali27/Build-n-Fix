<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Persistent shopping cart items owned by this user.
     *
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Stock movement entries recorded by this user.
     *
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Orders placed by this user.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Payments recorded for this user.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Create a new customer account with forced customer role and null branch.
     *
     * @param  array{name: string, email: string, phone: string, password: string}  $data
     */
    public static function registerCustomer(array $data): self
    {
        $user = new self([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => trim($data['phone']),
            'password' => $data['password'],
        ]);
        $user->role = 'customer';
        $user->branch = null;
        $user->save();

        return $user;
    }

    /**
     * Determine redirect destination after authentication.
     */
    public function homeUrl(): string
    {
        return match ($this->role) {
            'admin', 'owner' => '/dashboard',
            default => '/',
        };
    }

    /**
     * Format user data for the profile settings view.
     *
     * @return array{user_id: int, name: string, email: string, role: string, branch: string|null, status: string}
     */
    public function toProfileArray(): array
    {
        return [
            'user_id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role ?? 'customer',
            'branch' => $this->branch ?? null,
            'status' => 'Active',
        ];
    }
}
