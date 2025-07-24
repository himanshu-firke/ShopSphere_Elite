<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamlinedProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    protected $fillable = [
        'product_id',
        'url',
        'alt_text',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean'
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(StreamlinedProduct::class, 'product_id');
    }

    // Scopes
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
