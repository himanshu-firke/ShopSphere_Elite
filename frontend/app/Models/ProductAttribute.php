<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'name',
        'value',
        'type',
        'sort_order',
        'is_visible',
        'is_searchable',
        'is_filterable',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_visible' => 'boolean',
        'is_searchable' => 'boolean',
        'is_filterable' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the product that owns the attribute.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get only visible attributes.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope to get only searchable attributes.
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Scope to get only filterable attributes.
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Get the formatted value based on type.
     */
    public function getFormattedValueAttribute(): string
    {
        switch ($this->type) {
            case 'boolean':
                return $this->value ? 'Yes' : 'No';
            case 'number':
                return number_format($this->value);
            case 'currency':
                return '$' . number_format($this->value, 2);
            case 'percentage':
                return $this->value . '%';
            default:
                return $this->value;
        }
    }
} 