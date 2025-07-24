<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
        'options'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'options' => 'array'
    ];

    /**
     * Get the cart that owns the item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product for this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the subtotal for this item.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->price;
    }

    /**
     * Check if the item has sufficient stock.
     */
    public function hasStock(): bool
    {
        return $this->product->stock_quantity >= $this->quantity;
    }

    /**
     * Get the available stock for this item.
     */
    public function getAvailableStockAttribute(): int
    {
        return $this->product->stock_quantity;
    }

    /**
     * Check if the item is still active.
     */
    public function isActive(): bool
    {
        return $this->product->is_active;
    }

    /**
     * Check if the item is in stock.
     */
    public function isInStock(): bool
    {
        return $this->product->isInStock();
    }

    /**
     * Check if the item has options.
     */
    public function hasOptions(): bool
    {
        return !empty($this->options);
    }

    /**
     * Get formatted options for display.
     */
    public function getFormattedOptionsAttribute(): string
    {
        if (!$this->hasOptions()) {
            return '';
        }

        return collect($this->options)
            ->map(function ($value, $key) {
                return ucfirst($key) . ': ' . $value;
            })
            ->implode(', ');
    }

    /**
     * Get the validation rules for cart item options.
     */
    public static function getOptionValidationRules(): array
    {
        return [
            'options' => 'nullable|array',
            'options.size' => 'nullable|string|in:XS,S,M,L,XL,XXL',
            'options.color' => 'nullable|string|max:50',
            'options.style' => 'nullable|string|max:50',
            'options.material' => 'nullable|string|max:50',
            'options.customization' => 'nullable|string|max:255'
        ];
    }
} 