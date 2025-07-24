<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamlinedCartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2'
    ];

    // Relationships
    public function cart(): BelongsTo
    {
        return $this->belongsTo(StreamlinedCart::class, 'cart_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(StreamlinedProduct::class, 'product_id');
    }

    // Calculate total for this item
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}
