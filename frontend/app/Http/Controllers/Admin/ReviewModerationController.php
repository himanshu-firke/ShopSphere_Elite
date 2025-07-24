<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewModerationController extends Controller
{
    /**
     * Get pending reviews for moderation
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'product', 'images'])
            ->where('is_approved', false)
            ->orderBy('created_at', 'asc');

        // Apply filters
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->boolean('has_images')) {
            $query->has('images');
        }

        if ($request->boolean('verified_only')) {
            $query->where('is_verified_purchase', true);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $reviews = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'pending_count' => Review::where('is_approved', false)->count()
            ]
        ]);
    }

    /**
     * Approve a review
     */
    public function approve(Review $review): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Log moderation action
            $this->logModeration($review, 'approved');

            // Update review
            $review->update([
                'is_approved' => true
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review approved successfully',
                'data' => [
                    'review' => $review->fresh(['user', 'product', 'images'])
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a review
     */
    public function reject(Request $request, Review $review): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Log moderation action
            $this->logModeration($review, 'rejected', $request->reason);

            // Delete review images
            foreach ($review->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Delete review
            $review->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review rejected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve reviews
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $request->validate([
            'review_ids' => 'required|array',
            'review_ids.*' => 'integer|exists:reviews,id'
        ]);

        try {
            DB::beginTransaction();

            $reviews = Review::whereIn('id', $request->review_ids)
                ->where('is_approved', false)
                ->get();

            foreach ($reviews as $review) {
                // Log moderation action
                $this->logModeration($review, 'approved', 'Bulk approval');

                // Update review
                $review->update(['is_approved' => true]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($reviews) . ' reviews approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get moderation statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'pending_count' => Review::where('is_approved', false)->count(),
            'approved_count' => Review::where('is_approved', true)->count(),
            'total_count' => Review::count(),
            'with_images_count' => Review::has('images')->count(),
            'verified_purchase_count' => Review::where('is_verified_purchase', true)->count(),
            'rating_distribution' => $this->getRatingDistribution(),
            'moderation_history' => $this->getModerationHistory()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Log moderation action
     */
    private function logModeration(Review $review, string $action, ?string $reason = null): void
    {
        DB::table('review_moderation_log')->insert([
            'review_id' => $review->id,
            'product_id' => $review->product_id,
            'user_id' => $review->user_id,
            'moderator_id' => auth()->id(),
            'action' => $action,
            'reason' => $reason,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Get rating distribution for all reviews
     */
    private function getRatingDistribution(): array
    {
        $distribution = [];
        $total = Review::count();

        for ($rating = 5; $rating >= 1; $rating--) {
            $count = Review::where('rating', $rating)->count();
            $distribution[] = [
                'rating' => $rating,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100) : 0
            ];
        }

        return $distribution;
    }

    /**
     * Get recent moderation history
     */
    private function getModerationHistory(int $limit = 50): array
    {
        return DB::table('review_moderation_log')
            ->join('users as moderators', 'moderators.id', '=', 'review_moderation_log.moderator_id')
            ->join('users as reviewers', 'reviewers.id', '=', 'review_moderation_log.user_id')
            ->join('products', 'products.id', '=', 'review_moderation_log.product_id')
            ->select([
                'review_moderation_log.*',
                'moderators.name as moderator_name',
                'reviewers.name as reviewer_name',
                'products.name as product_name'
            ])
            ->orderBy('review_moderation_log.created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
} 