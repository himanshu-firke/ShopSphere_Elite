<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewHelpful;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Get reviews for a product with filters and pagination
     */
    public function index(Request $request, Product $product): JsonResponse
    {
        $query = $product->reviews()
            ->with(['user', 'images'])
            ->where('is_approved', true);

        // Apply rating filter
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        // Apply verified purchase filter
        if ($request->boolean('verified_only')) {
            $query->where('is_verified_purchase', true);
        }

        // Apply has images filter
        if ($request->boolean('with_images')) {
            $query->has('images');
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch ($sortBy) {
            case 'helpful':
                $query->orderBy('helpful_votes', $sortOrder);
                break;
            case 'rating':
                $query->orderBy('rating', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $perPage = min($request->get('per_page', 10), 50);
        $reviews = $query->paginate($perPage);

        // Add helpful status for authenticated user
        if (Auth::check()) {
            $userId = Auth::id();
            $reviews->getCollection()->transform(function ($review) use ($userId) {
                $review->is_helpful = $review->helpfulVotes()
                    ->where('user_id', $userId)
                    ->exists();
                return $review;
            });
        }

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'summary' => [
                    'average_rating' => $product->average_rating,
                    'total_reviews' => $product->review_count,
                    'rating_distribution' => $this->getRatingDistribution($product)
                ]
            ]
        ]);
    }

    /**
     * Create a new review
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check if user has already reviewed this product
            if ($product->reviews()->where('user_id', Auth::id())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this product'
                ], 422);
            }

            // Check if user has purchased the product
            $isVerifiedPurchase = $product->orders()
                ->where('user_id', Auth::id())
                ->where('status', 'delivered')
                ->exists();

            // Create review
            $review = $product->reviews()->create([
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'is_verified_purchase' => $isVerifiedPurchase,
                'is_approved' => !config('app.review_moderation_enabled', true),
                'helpful_votes' => 0,
                'unhelpful_votes' => 0
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $paths = $this->imageService->storeProductImage($image);
                    
                    $review->images()->create([
                        'image_path' => $paths['original'],
                        'is_active' => true
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => config('app.review_moderation_enabled', true)
                    ? 'Review submitted and pending approval'
                    : 'Review published successfully',
                'data' => [
                    'review' => $review->load(['user', 'images'])
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing review
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        // Check if user owns the review
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|between:1,5',
            'title' => 'sometimes|string|max:255',
            'comment' => 'sometimes|string|max:1000',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
            'remove_image_ids' => 'sometimes|array',
            'remove_image_ids.*' => 'integer|exists:review_images,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update review
            $review->update($request->only([
                'rating', 'title', 'comment'
            ]));

            // Handle image removals
            if ($request->has('remove_image_ids')) {
                foreach ($request->remove_image_ids as $imageId) {
                    $image = $review->images()->find($imageId);
                    if ($image) {
                        $this->imageService->deleteProductImage(basename($image->image_path));
                        $image->delete();
                    }
                }
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $paths = $this->imageService->storeProductImage($image);
                    
                    $review->images()->create([
                        'image_path' => $paths['original'],
                        'is_active' => true
                    ]);
                }
            }

            // Reset approval status if moderation is enabled
            if (config('app.review_moderation_enabled', true)) {
                $review->update(['is_approved' => false]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => config('app.review_moderation_enabled', true)
                    ? 'Review updated and pending approval'
                    : 'Review updated successfully',
                'data' => [
                    'review' => $review->fresh(['user', 'images'])
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a review
     */
    public function destroy(Review $review): JsonResponse
    {
        // Check if user owns the review
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Delete review images
            foreach ($review->images as $image) {
                $this->imageService->deleteProductImage(basename($image->image_path));
            }

            // Delete review and related data
            $review->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle helpful status for a review
     */
    public function toggleHelpful(Review $review): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $helpfulVote = $review->helpfulVotes()
                ->where('user_id', $userId)
                ->first();

            if ($helpfulVote) {
                // Remove helpful vote
                $helpfulVote->delete();
                $review->decrement('helpful_votes');
                $isHelpful = false;
            } else {
                // Add helpful vote
                $review->helpfulVotes()->create([
                    'user_id' => $userId
                ]);
                $review->increment('helpful_votes');
                $isHelpful = true;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isHelpful ? 'Marked as helpful' : 'Removed helpful mark',
                'data' => [
                    'is_helpful' => $isHelpful,
                    'helpful_votes' => $review->fresh()->helpful_votes
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update helpful status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rating distribution for a product
     */
    private function getRatingDistribution(Product $product): array
    {
        $distribution = [];
        $total = $product->approvedReviews()->count();

        for ($rating = 5; $rating >= 1; $rating--) {
            $count = $product->approvedReviews()
                ->where('rating', $rating)
                ->count();

            $distribution[] = [
                'rating' => $rating,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100) : 0
            ];
        }

        return $distribution;
    }
} 