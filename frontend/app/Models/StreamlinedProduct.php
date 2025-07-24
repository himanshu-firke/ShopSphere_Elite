<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StreamlinedProduct extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'sku',
        'stock_quantity',
        'category_id',
        'is_featured',
        'status',
        'average_rating',
        'reviews_count'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'is_featured' => 'boolean',
        'stock_quantity' => 'integer',
        'reviews_count' => 'integer'
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(StreamlinedCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(StreamlinedProductImage::class, 'product_id');
    }

    public function primaryImage()
    {
        return $this->hasOne(StreamlinedProductImage::class, 'product_id')->where('is_primary', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(StreamlinedReview::class, 'product_id');
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', 'approved');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    public function getFormattedSalePriceAttribute()
    {
        return $this->sale_price ? number_format($this->sale_price, 2) : null;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->sale_price && $this->sale_price < $this->price) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getIsOnSaleAttribute()
    {
        return $this->sale_price && $this->sale_price < $this->price;
    }

    // API Resource format matching frontend interface
    public function toFrontendArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'sku' => $this->sku,
            'stock_quantity' => $this->stock_quantity,
            'category_id' => $this->category_id,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'alt_text' => $image->alt_text,
                    'is_primary' => $image->is_primary,
                ];
            })->toArray(),
            'average_rating' => (float) $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'is_featured' => $this->is_featured,
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
