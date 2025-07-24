<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use App\Services\WishlistService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    protected $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user's wishlist
     */
    public function index(Request $request): JsonResponse
    {
        $wishlist = $this->wishlistService->getWishlist($request->user());

        return response()->json([
            'success' => true,
            'data' => $wishlist
        ]);
    }

    /**
     * Add item to wishlist
     */
    public function addItem(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $item = $this->wishlistService->addToWishlist(
                $request->user(),
                $product,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Item added to wishlist',
                'data' => [
                    'item' => $item->load('product.primaryImage'),
                    'total_items' => $this->wishlistService->getItemCount($request->user())
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove item from wishlist
     */
    public function removeItem(Request $request, Wishlist $item): JsonResponse
    {
        try {
            $this->wishlistService->removeFromWishlist($request->user(), $item);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from wishlist',
                'data' => [
                    'total_items' => $this->wishlistService->getItemCount($request->user())
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Clear wishlist
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $this->wishlistService->clearWishlist($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Wishlist cleared'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Move item to cart
     */
    public function moveToCart(Request $request, Wishlist $item): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'integer|min:1|max:99'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->wishlistService->moveToCart(
                $request->user(),
                $item,
                $request->quantity ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'Item moved to cart',
                'data' => [
                    'total_items' => $this->wishlistService->getItemCount($request->user())
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Move all items to cart
     */
    public function moveAllToCart(Request $request): JsonResponse
    {
        try {
            $results = $this->wishlistService->moveAllToCart($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Items moved to cart',
                'data' => [
                    'results' => $results,
                    'total_items' => $this->wishlistService->getItemCount($request->user())
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Check if product is in wishlist
     */
    public function check(Request $request, Product $product): JsonResponse
    {
        try {
            $isInWishlist = $this->wishlistService->isInWishlist(
                $request->user(),
                $product
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'in_wishlist' => $isInWishlist
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update item notes
     */
    public function updateNotes(Request $request, Wishlist $item): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->wishlistService->updateNotes(
                $request->user(),
                $item,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Notes updated',
                'data' => [
                    'item' => $item->fresh(['product.primaryImage'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
} 