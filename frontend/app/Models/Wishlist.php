<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'notes'
    ];

    /**
     * Get the user that owns the wishlist item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product in the wishlist.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if the product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->product->isInStock();
    }

    /**
     * Check if the product is active.
     */
    public function isActive(): bool
    {
        return $this->product->is_active;
    }

    /**
     * Get the current price of the product.
     */
    public function getCurrentPriceAttribute(): float
    {
        return $this->product->current_price;
    }

    /**
     * Get the discount percentage of the product.
     */
    public function getDiscountPercentageAttribute(): int
    {
        return $this->product->discount_percentage;
    }

    /**
     * Check if the product is on sale.
     */
    public function isOnSale(): bool
    {
        return $this->product->is_on_sale;
    }

    /**
     * Get the date the item was added to the wishlist.
     */
    public function getAddedDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
} 