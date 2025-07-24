<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'title',
        'comment',
        'is_approved',
        'is_verified_purchase',
        'helpful_votes',
        'unhelpful_votes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'is_verified_purchase' => 'boolean',
        'helpful_votes' => 'integer',
        'unhelpful_votes' => 'integer',
    ];

    /**
     * Get the product that owns the review.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the review images.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class);
    }

    /**
     * Get the helpful votes for this review.
     */
    public function helpfulVotes(): HasMany
    {
        return $this->hasMany(ReviewHelpful::class);
    }

    /**
     * Scope to get only approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope to get only verified purchase reviews.
     */
    public function scopeVerifiedPurchase($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Get the helpful percentage.
     */
    public function getHelpfulPercentageAttribute(): float
    {
        $total = $this->helpful_votes + $this->unhelpful_votes;
        if ($total === 0) {
            return 0;
        }
        return round(($this->helpful_votes / $total) * 100, 1);
    }

    /**
     * Check if the review is helpful.
     */
    public function isHelpful(): bool
    {
        return $this->helpful_percentage >= 50;
    }

    /**
     * Get the rating stars as an array.
     */
    public function getRatingStarsAttribute(): array
    {
        $stars = [];
        for ($i = 1; $i <= 5; $i++) {
            $stars[] = $i <= $this->rating;
        }
        return $stars;
    }
} 