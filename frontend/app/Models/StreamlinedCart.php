<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StreamlinedCart extends Model
{
    use HasFactory;

    protected $table = 'cart';

    protected $fillable = [
        'user_id',
        'session_id',
        'item_count',
        'subtotal',
        'tax',
        'discount',
        'total'
    ];

    protected $casts = [
        'item_count' => 'integer',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StreamlinedCartItem::class, 'cart_id');
    }

    // Methods to calculate totals
    public function calculateTotals()
    {
        $subtotal = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $this->item_count = $this->items->sum('quantity');
        $this->subtotal = $subtotal;
        $this->tax = $subtotal * 0.1; // 10% tax
        $this->total = $this->subtotal + $this->tax - $this->discount;
        
        $this->save();
    }

    // API Resource format matching frontend interface
    public function toFrontendArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'session_id' => $this->session_id,
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                        'price' => (float) $item->product->price,
                        'sale_price' => $item->product->sale_price ? (float) $item->product->sale_price : null,
                        'sku' => $item->product->sku,
                        'primary_image' => $item->product->primaryImage ? [
                            'url' => $item->product->primaryImage->url,
                            'alt_text' => $item->product->primaryImage->alt_text,
                        ] : null,
                    ],
                ];
            })->toArray(),
            'item_count' => $this->item_count,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
