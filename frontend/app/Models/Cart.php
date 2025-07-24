<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'subtotal',
        'tax',
        'discount',
        'total',
        'coupon_code',
        'notes',
        'expires_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'expires_at' => 'datetime'
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate cart totals
     */
    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $tax = $subtotal * config('cart.tax_rate', 0.1); // 10% tax by default
        $discount = 0;

        // Apply coupon discount if available
        if ($this->coupon_code) {
            // TODO: Implement coupon system
            // $discount = $this->calculateDiscount($subtotal);
        }

        $total = $subtotal + $tax - $discount;

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total
        ]);
    }

    /**
     * Add an item to the cart
     */
    public function addItem(Product $product, int $quantity = 1, array $options = []): CartItem
    {
        $existingItem = $this->items()
            ->where('product_id', $product->id)
            ->where('options', json_encode($options))
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            return $existingItem;
        }

        $item = $this->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->current_price,
            'options' => $options
        ]);

        $this->calculateTotals();

        return $item;
    }

    /**
     * Update item quantity
     */
    public function updateItemQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }

        $this->calculateTotals();
    }

    /**
     * Remove an item from the cart
     */
    public function removeItem(CartItem $item): void
    {
        $item->delete();
        $this->calculateTotals();
    }

    /**
     * Clear all items from the cart
     */
    public function clear(): void
    {
        $this->items()->delete();
        $this->calculateTotals();
    }

    /**
     * Get the number of items in the cart
     */
    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Check if the cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }

    /**
     * Check if the cart has items
     */
    public function hasItems(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Check if the cart belongs to a guest
     */
    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Check if the cart belongs to a user
     */
    public function isUser(): bool
    {
        return !$this->isGuest();
    }

    /**
     * Check if the cart has expired
     */
    public function hasExpired(): bool
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    /**
     * Extend cart expiration
     */
    public function extend(int $minutes = 60): void
    {
        $this->update([
            'expires_at' => now()->addMinutes($minutes)
        ]);
    }

    /**
     * Convert cart to order
     */
    public function toOrder(): array
    {
        return [
            'user_id' => $this->user_id,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total' => $this->total,
            'coupon_code' => $this->coupon_code,
            'notes' => $this->notes,
            'items' => $this->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'options' => $item->options
                ];
            })->toArray()
        ];
    }
} 